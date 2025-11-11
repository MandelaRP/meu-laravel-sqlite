<script setup>
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogTrigger, DialogClose } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import MoneyInput from '@/components/MoneyInput.vue';
import { CheckIcon, Loader2Icon, PlusIcon, Eye, Copy, CheckCircle2, AlertCircle } from 'lucide-vue-next';
import { Smartphone, Wifi } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import qrcodeSvg from 'qrcode.vue';

const props = defineProps({
    acquirerFixedFee: {
        type: Number,
        default: 1.00
    },
    acquirerPercentageFee: {
        type: Number,
        default: 0
    },
    gatewayPixFixed: {
        type: Number,
        default: 0.04
    },
    gatewayPixPercentage: {
        type: Number,
        default: 0
    }
});

const generateChargeForm = useForm({
    amount: 0,
});

// Armazenar valor original para exibição precisa
const originalAmount = ref(0);

// Cálculo dinâmico de taxas (usando precisão de 2 casas decimais)
const calculatedFees = computed(() => {
    const amount = parseFloat(generateChargeForm.amount) || 0;
    if (amount <= 0) {
        return {
            totalFees: 0,
            netAmount: 0
        };
    }
    
    // Garantir precisão de 2 casas decimais
    const amountStr = amount.toFixed(2);
    const amountNum = parseFloat(amountStr);
    
    // Taxa da adquirente (fixa + percentual)
    const acquirerFixedFee = props.acquirerFixedFee || 0;
    const acquirerPercentageFee = props.acquirerPercentageFee || 0;
    
    // Taxa da gateway (LuckPay)
    const gatewayFixed = props.gatewayPixFixed || 0;
    const gatewayPercentage = props.gatewayPixPercentage || 0;
    
    // Lógica: Se taxa LuckPay > 0, ela já engloba a taxa da adquirente
    // Se taxa LuckPay = 0, aplicar apenas taxa da adquirente
    let totalFees = 0;
    
    if (gatewayFixed > 0 || gatewayPercentage > 0) {
        // Taxa total = taxa fixa LuckPay + (valor * taxa percentual LuckPay / 100)
        const gatewayPercentageAmount = parseFloat(((amountNum * gatewayPercentage) / 100).toFixed(2));
        totalFees = parseFloat((gatewayFixed + gatewayPercentageAmount).toFixed(2));
    } else {
        // Taxa total = taxa fixa adquirente + (valor * taxa percentual adquirente / 100)
        const acquirerPercentageAmount = parseFloat(((amountNum * acquirerPercentageFee) / 100).toFixed(2));
        totalFees = parseFloat((acquirerFixedFee + acquirerPercentageAmount).toFixed(2));
    }
    
    // Valor líquido (valor bruto - taxa total)
    const netAmount = parseFloat((amountNum - totalFees).toFixed(2));
    
    return {
        totalFees: Math.max(0, totalFees),
        netAmount: Math.max(0, netAmount)
    };
});

// Formatar moeda
const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
};

const showQrcode = ref(false);
const animationStep = ref('waiting');
const copySuccess = ref(false);
const loadingGenerateCharge = ref(false);
const paymentCompleted = ref(false);
const showPaymentAnimation = ref(false);
const responseGenerateCharge = ref(null);
const pixCode = ref(''); // Variável reativa para o código PIX
let paymentTimer = null;
let paymentPollingInterval = null;
const currentSaleId = ref(null);

const handleConfirm = () => {
    loadingGenerateCharge.value = true;
    // Armazenar valor original antes de enviar
    originalAmount.value = parseFloat(generateChargeForm.amount) || 0;
    axios.post(route('sale.generate-charge'), {
        amount: generateChargeForm.amount,
    }).then((response) => {
        console.log('Resposta completa da API:', response);
        console.log('Dados da resposta:', response.data);
        console.log('Estrutura qrcode:', response.data?.qrcode);
        console.log('Content do qrcode:', response.data?.qrcode?.content);
        
        if (response.status === 200 && response.data) {
            // Verificar se a resposta tem o formato esperado
            if (response.data.status === 'success' && response.data.qrcode && response.data.qrcode.content) {
                console.log('QR Code content encontrado:', response.data.qrcode.content);
                console.log('Tamanho do código:', response.data.qrcode.content.length);
                
                // Garantir que responseGenerateCharge tenha a estrutura correta
                responseGenerateCharge.value = response;
                
                // Extrair o código PIX diretamente e armazenar em variável reativa
                const code = response.data.qrcode?.content || '';
                pixCode.value = code;
                
                // Debug: verificar se o código está acessível
                console.log('responseGenerateCharge após atribuição:', responseGenerateCharge.value);
                console.log('responseGenerateCharge.data:', responseGenerateCharge.value.data);
                console.log('responseGenerateCharge.data.qrcode:', responseGenerateCharge.value.data?.qrcode);
                console.log('responseGenerateCharge.data.qrcode.content:', responseGenerateCharge.value.data?.qrcode?.content);
                console.log('pixCode.value:', pixCode.value);
                console.log('Código PIX extraído:', code);
                
                currentSaleId.value = response.data.sale_id;
                showQrcode.value = true;
                loadingGenerateCharge.value = false;
                animationStep.value = 'waiting';

                // Iniciar polling para verificar status do pagamento
                startPaymentPolling();
            } else {
                // Resposta sem QR code
                console.error('Resposta sem QR code válido:', response.data);
                console.error('Estrutura completa:', JSON.stringify(response.data, null, 2));
                alert('Erro: Não foi possível gerar o QR code. ' + (response.data.message || 'Tente novamente.'));
                loadingGenerateCharge.value = false;
            }
        } else {
            console.error('Resposta inválida:', response);
            alert('Erro ao gerar cobrança. Tente novamente.');
            loadingGenerateCharge.value = false;
        }
    }).catch((error) => {
        console.error('Erro na requisição:', error);
        const errorMessage = error.response?.data?.message || error.message || 'Erro ao gerar cobrança. Verifique se a adquirente está configurada corretamente.';
        alert('Erro: ' + errorMessage);
        loadingGenerateCharge.value = false;
    });
}

const startPaymentPolling = () => {
    // Limpar qualquer polling anterior
    if (paymentPollingInterval) {
        clearInterval(paymentPollingInterval);
    }

    // Verificar status a cada 1 segundo para resposta instantânea
    paymentPollingInterval = setInterval(() => {
        if (!currentSaleId.value) {
            return;
        }

        axios.get(route('sale.check-status', currentSaleId.value))
            .then((response) => {
                if (response.data.status === 'success') {
                    const paymentStatus = response.data.payment_status;
                    
                    // Log para debug
                    console.log('Status do pagamento:', paymentStatus, response.data);

                    // Verificar se foi pago (aceitar 'paid' ou qualquer variação)
                    if (paymentStatus === 'paid' || paymentStatus === 'pago' || paymentStatus === 'approved') {
                        // Pagamento confirmado - atualizar imediatamente
                        clearInterval(paymentPollingInterval);
                        paymentPollingInterval = null;
                        
                        // Atualizar status imediatamente sem delay
                        animationStep.value = 'completed';
                        showPaymentAnimation.value = true;
                        paymentCompleted.value = true;
                        
                        console.log('Pagamento confirmado! Atualizando UI...');
                        
                        // Recarregar a página após 2 segundos para atualizar dados
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else if (paymentStatus === 'expired' || paymentStatus === 'cancelled') {
                        // Pagamento expirado ou cancelado
                        clearInterval(paymentPollingInterval);
                        paymentPollingInterval = null;
                        alert('O pagamento foi ' + (paymentStatus === 'expired' ? 'expirado' : 'cancelado') + '. Por favor, gere um novo QR code.');
                        resetModal();
                    }
                    // Se ainda estiver 'pending' ou 'waiting_payment', continua aguardando
                }
            })
            .catch((error) => {
                console.error('Erro ao verificar status do pagamento:', error);
                // Não interromper o polling por erro, apenas logar
            });
    }, 1000); // Verificar a cada 1 segundo para resposta instantânea
}

const copyToClipboard = async () => {
    try {
        const codeToCopy = pixCode.value || responseGenerateCharge.value?.data?.qrcode?.content;
        if (!codeToCopy) {
            console.error('Código PIX não disponível para copiar');
            return;
        }
        await navigator.clipboard.writeText(codeToCopy);
        copySuccess.value = true;
        setTimeout(() => {
            copySuccess.value = false;
        }, 2000);
    } catch (err) {
        console.error('Erro ao copiar:', err);
    }
}

const resetModal = () => {
    showQrcode.value = false;
    paymentCompleted.value = false;
    showPaymentAnimation.value = false;
    animationStep.value = 'waiting';
    responseGenerateCharge.value = null;
    pixCode.value = ''; // Limpar código PIX
    generateChargeForm.amount = 0;
    originalAmount.value = 0; // Limpar valor original
    copySuccess.value = false;
    currentSaleId.value = null;

    // Limpar timers e polling
    if (paymentTimer) {
        clearTimeout(paymentTimer);
        paymentTimer = null;
    }
    
    if (paymentPollingInterval) {
        clearInterval(paymentPollingInterval);
        paymentPollingInterval = null;
    }
}
</script>

<template>
    <div>
        <Dialog @update:open="(open) => { if (open) resetModal(); }">
                    <DialogTrigger as-child>
                        <slot name="trigger">
                            <Button variant="outline" @click="resetModal">
                                <PlusIcon class="h-4 w-4" />
                                Nova Venda
                            </Button>
                        </slot>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-lg">
                        <!-- Formulário inicial -->
                        <div class="space-y-6" v-show="!showQrcode">
                            <DialogHeader class="text-center">
                                <DialogTitle class="text-xl">Nova Venda</DialogTitle>
                                <DialogDescription class="text-base">
                                    Adicione uma nova transação para o seu dashboard.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="space-y-4">
                                <MoneyInput 
                                    v-model="generateChargeForm.amount" 
                                    label="Valor a Depositar"
                                />
                                
                                <!-- Exibição dinâmica de taxas e valor líquido -->
                                <div v-if="generateChargeForm.amount > 0" class="space-y-3 pt-3 border-t">
                                    <div class="flex justify-between items-center">
                                        <Label class="text-sm text-muted-foreground">Taxas:</Label>
                                        <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                            {{ formatCurrency(calculatedFees.totalFees) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <Label class="text-sm text-muted-foreground">Valor Líquido:</Label>
                                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                                            {{ formatCurrency(calculatedFees.netAmount) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <DialogFooter class="flex items-center justify-between gap-3 w-full">
                                <DialogClose as-child>
                                    <Button :disabled="loadingGenerateCharge" type="button" variant="secondary"
                                        class="w-full">
                                        Cancelar
                                    </Button>
                                </DialogClose>
                                <Button @click="handleConfirm" :disabled="loadingGenerateCharge" type="submit"
                                    class="w-full">
                                    <CheckIcon v-if="!loadingGenerateCharge" class="h-4 w-4" />
                                    <Loader2Icon v-else class="h-4 w-4 animate-spin" />
                                    Confirmar
                                </Button>
                            </DialogFooter>
                        </div>

                        <!-- QR Code com Animação de Pagamento Melhorada -->
                        <div class="space-y-6 flex flex-col items-center justify-center w-full relative min-h-[500px]"
                            v-if="showQrcode">

                            <!-- Estado: Aguardando Pagamento -->
                            <Transition name="slide-fade" mode="out-in">
                                <div v-if="animationStep === 'waiting'" class="text-center space-y-6 w-full">
                                    <!-- Header com ícone -->
                                    <div class="flex flex-col items-center space-y-3">
                                        <div class="relative">
                                            <div
                                                class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                                <Smartphone class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div class="absolute -top-1 -right-1">
                                                <div
                                                    class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center animate-pulse">
                                                    <Wifi class="w-3 h-3 text-white" />
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <h2 class="text-xl font-bold text-foreground">Aguardando pagamento</h2>
                                            <p class="text-sm text-muted-foreground mt-1">
                                                Escaneie o QR Code com seu aplicativo de pagamento
                                            </p>
                                        </div>
                                    </div>

                                    <!-- QR Code com design melhorado -->
                                    <div class="relative mx-auto w-fit" v-if="responseGenerateCharge && responseGenerateCharge.data && responseGenerateCharge.data.qrcode && responseGenerateCharge.data.qrcode.content">
                                        <div
                                            class="relative p-6 bg-white dark:bg-gray-900 rounded-2xl shadow-lg border-2 border-dashed border-blue-200 dark:border-blue-800">
                                            <qrcode-svg :value="pixCode || responseGenerateCharge?.data?.qrcode?.content || ''" :size="220"
                                                level="H" class="rounded-lg" />
                                            <!-- Cantos decorativos -->
                                            <div
                                                class="absolute top-2 left-2 w-4 h-4 border-l-2 border-t-2 border-blue-500 rounded-tl-lg">
                                            </div>
                                            <div
                                                class="absolute top-2 right-2 w-4 h-4 border-r-2 border-t-2 border-blue-500 rounded-tr-lg">
                                            </div>
                                            <div
                                                class="absolute bottom-2 left-2 w-4 h-4 border-l-2 border-b-2 border-blue-500 rounded-bl-lg">
                                            </div>
                                            <div
                                                class="absolute bottom-2 right-2 w-4 h-4 border-r-2 border-b-2 border-blue-500 rounded-br-lg">
                                            </div>
                                        </div>

                                        <!-- Indicador de status -->
                                        <div class="absolute -top-3 -right-3">
                                            <div class="relative">
                                                <div
                                                    class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center animate-pulse">
                                                    <div class="w-3 h-3 bg-white rounded-full"></div>
                                                </div>
                                                <div
                                                    class="absolute inset-0 w-8 h-8 bg-blue-500 rounded-full animate-ping opacity-30">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Valor da transação -->
                                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 w-full">
                                        <div class="text-center">
                                            <p class="text-sm text-muted-foreground">Valor a receber</p>
                                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                                R$ {{ originalAmount.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Campo de cópia melhorado -->
                                    <div class="w-full space-y-3">
                                        <Label class="text-sm font-medium">Código PIX Copia e Cola</Label>
                                        <div class="flex items-center space-x-2">
                                            <Input 
                                                v-model="pixCode"
                                                readonly
                                                class="font-mono text-xs"
                                                placeholder="Aguardando código PIX..."
                                            />
                                            <Button 
                                                @click="copyToClipboard" 
                                                size="sm" 
                                                variant="outline"
                                                :disabled="!pixCode"
                                                class="px-3 transition-all duration-200"
                                                :class="copySuccess ? 'bg-green-50 border-green-200 text-green-700' : ''"
                                            >
                                                <CheckCircle2 v-if="copySuccess" class="w-4 h-4" />
                                                <Copy v-else class="w-4 h-4" />
                                            </Button>
                                        </div>
                                        <p v-if="copySuccess" class="text-xs text-green-600 flex items-center gap-1">
                                            <CheckCircle2 class="w-3 h-3" />
                                            Código copiado com sucesso!
                                        </p>
                                        <p v-if="!pixCode" class="text-xs text-muted-foreground">
                                            Carregando código PIX...
                                        </p>
                                    </div>
                                </div>
                            </Transition>

                            <!-- Estado: Processando Pagamento -->
                            <Transition name="slide-fade" mode="out-in">
                                <div v-if="animationStep === 'processing'" class="text-center space-y-6 w-full">
                                    <div class="flex flex-col items-center space-y-4">
                                        <div class="relative">
                                            <div
                                                class="w-20 h-20 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center animate-pulse">
                                                <Loader2Icon class="w-10 h-10 text-white animate-spin" />
                                            </div>
                                            <div
                                                class="absolute inset-0 w-20 h-20 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full animate-ping opacity-20">
                                            </div>
                                        </div>
                                        <div>
                                            <h2 class="text-xl font-bold text-yellow-600 dark:text-yellow-400">
                                                Processando pagamento...
                                            </h2>
                                            <p class="text-sm text-muted-foreground mt-1">
                                                Aguarde, estamos confirmando seu pagamento
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Barra de progresso animada -->
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                        <div
                                            class="bg-gradient-to-r from-yellow-400 to-orange-500 h-2 rounded-full animate-pulse progress-bar">
                                        </div>
                                    </div>

                                    <!-- Informações do pagamento -->
                                    <div
                                        class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-4 w-full border border-yellow-200 dark:border-yellow-800">
                                        <div class="flex items-center justify-center space-x-2">
                                            <AlertCircle class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                                Não feche esta janela durante o processamento
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </Transition>

                            <!-- Estado: Pagamento Concluído -->
                            <Transition name="slide-fade" mode="out-in">
                                <div v-if="animationStep === 'completed'" class="text-center space-y-6 w-full">
                                    <div class="flex flex-col items-center space-y-4">
                                        <div class="relative">
                                            <div
                                                class="w-24 h-24 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full flex items-center justify-center animate-bounce">
                                                <CheckIcon class="w-12 h-12 text-white animate-pulse" />
                                            </div>
                                            <div
                                                class="absolute inset-0 w-24 h-24 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full animate-ping opacity-20">
                                            </div>
                                        </div>
                                        <div>
                                            <h2 class="text-xl font-bold text-green-600 dark:text-green-400">
                                                Pagamento Concluído!
                                            </h2>
                                            <p class="text-sm text-muted-foreground mt-1">
                                                O pagamento foi processado com sucesso
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Resumo da transação -->
                                    <div
                                        class="bg-green-50 dark:bg-green-900/20 rounded-xl p-6 w-full border border-green-200 dark:border-green-800">
                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-muted-foreground">Valor recebido:</span>
                                                <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                                    {{ formatCurrency(calculatedFees.netAmount) }}
                                                </span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-muted-foreground">Data/Hora:</span>
                                                <span class="text-sm font-medium">
                                                    {{ new Date().toLocaleString('pt-BR') }}
                                                </span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-muted-foreground">Método:</span>
                                                <span class="text-sm font-medium">PIX</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botão de ação -->
                                    <div class="pt-2">
                                        <DialogClose as-child>
                                            <Button class="w-full h-12 text-base" @click="resetModal">
                                                <Eye class="w-5 h-5 mr-2" />
                                                Ver Transação
                                            </Button>
                                        </DialogClose>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </DialogContent>
                </Dialog>
    </div>
</template>