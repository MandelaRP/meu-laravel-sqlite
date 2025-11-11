<script setup>
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogTrigger, DialogClose } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import MoneyInput from '@/components/MoneyInput.vue';
import { CheckIcon, Loader2Icon, PlusIcon, AlertCircle } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { useToast } from '@/components/ui/toast/use-toast';

const props = defineProps({
    availableBalance: {
        type: Number,
        default: 0
    },
    minWithdraw: {
        type: Number,
        default: 10.00
    },
    fixedWithdrawFee: {
        type: Number,
        default: 5.00
    },
    percentWithdrawFee: {
        type: Number,
        default: 0
    },
    acquirerWithdrawalFee: {
        type: Number,
        default: 0.50
    },
});

const { toast } = useToast();

const withdrawalForm = useForm({
    amount: 0,
    pix_key_type: 'CPF',
    pix_key: '',
    pix_key_description: '',
});

const showWithdrawalModal = ref(false);
const loadingWithdrawal = ref(false);
const showConfirmationModal = ref(false);
const withdrawalConfirmation = ref(null);

// Calcular taxas e valor líquido em tempo real
const calculatedWithdrawal = computed(() => {
    // Converter valor para número (MoneyInput retorna número via unformat)
    let amount = withdrawalForm.amount;
    if (amount === null || amount === undefined || amount === '') {
        amount = 0;
    } else if (typeof amount === 'string') {
        // Se for string, remover formatação e converter
        amount = parseFloat(amount.toString().replace(/[^\d,.-]/g, '').replace(',', '.')) || 0;
    } else {
        amount = parseFloat(amount) || 0;
    }
    
    if (amount <= 0 || isNaN(amount)) {
        return {
            fee: 0,
            netAmount: 0
        };
    }
    
    // Taxa da adquirente (FullPix ou LiberPay)
    const taxaAdquirente = parseFloat(props.acquirerWithdrawalFee) || 0;
    
    // Taxa da LuckPay (fixa + percentual)
    const percentFee = parseFloat(((amount * props.percentWithdrawFee) / 100).toFixed(2));
    const taxaFixaLuckPay = parseFloat(props.fixedWithdrawFee) || 0;
    const taxaTotalLuckPay = parseFloat((taxaFixaLuckPay + percentFee).toFixed(2));
    
    // Lógica: Se taxa LuckPay > 0, ela já engloba a taxa da adquirente quando maior
    // Se taxa LuckPay = 0, usar apenas taxa da adquirente
    // Se taxa LuckPay > taxa adquirente, usar taxa LuckPay (engloba)
    // Se taxa LuckPay < taxa adquirente, usar taxa adquirente (LuckPay não cobre)
    let taxaTotal;
    if (taxaTotalLuckPay > 0) {
        if (taxaTotalLuckPay >= taxaAdquirente) {
            // LuckPay cobra mais ou igual → engloba taxa da adquirente
            taxaTotal = taxaTotalLuckPay;
        } else {
            // LuckPay cobra menos → usar apenas taxa da adquirente
            taxaTotal = taxaAdquirente;
        }
    } else {
        // LuckPay não cobra taxa → apenas adquirente
        taxaTotal = taxaAdquirente;
    }
    
    // Valor líquido que o seller receberá (após todas as taxas)
    const netAmount = parseFloat((amount - taxaTotal).toFixed(2));
    
    return {
        fee: Math.max(0, parseFloat(taxaTotal.toFixed(2))),
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

// Validar chave PIX baseado no tipo
const validatePixKey = () => {
    try {
        const type = withdrawalForm.pix_key_type;
        const key = (withdrawalForm.pix_key || '').trim();
        
        if (!key || key === '') {
            return false;
        }
        
        switch (type) {
            case 'CPF':
                const cpf = key.replace(/\D/g, '');
                return cpf.length === 11;
            case 'CNPJ':
                const cnpj = key.replace(/\D/g, '');
                return cnpj.length === 14;
            case 'EMAIL':
                // Validar email com regex mais permissivo
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const isValidEmail = emailRegex.test(key) && key.length >= 5 && key.length <= 255;
                // Log para debug (remover em produção)
                if (!isValidEmail) {
                    console.log('Email inválido:', key, 'regex test:', emailRegex.test(key), 'length:', key.length);
                }
                return isValidEmail;
            case 'PHONE':
                const phone = key.replace(/\D/g, '');
                return phone.length >= 10 && phone.length <= 11;
            case 'EVP':
                return /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(key);
            default:
                return false;
        }
    } catch (error) {
        console.error('Erro ao validar chave PIX:', error);
        return false;
    }
};

// Validar valor mínimo (R$ 10,00 padrão) e valor líquido mínimo (R$ 5,00)
const validateMinNetAmount = () => {
    const amount = parseFloat(withdrawalForm.amount) || 0;
    const minWithdraw = parseFloat(props.minWithdraw) || 10.00;
    const netAmount = calculatedWithdrawal.value.netAmount || 0;
    const minNetAmount = 5.00;
    
    if (amount > 0 && amount < minWithdraw) {
        return {
            valid: false,
            message: `O valor mínimo para saque é de ${formatCurrency(minWithdraw)}. Após a taxa, você receberá ${formatCurrency(netAmount)} líquidos.`,
            minRequired: minWithdraw
        };
    }
    
    if (amount > 0 && netAmount < minNetAmount) {
        const fee = calculatedWithdrawal.value.fee || 0;
        const valorNecessario = minNetAmount + fee;
        return {
            valid: false,
            message: `O valor líquido mínimo exigido é ${formatCurrency(minNetAmount)}. O valor mínimo para saque é de ${formatCurrency(valorNecessario)} (já incluindo as taxas).`,
            minRequired: valorNecessario
        };
    }
    
    return { valid: true };
};

// Computed para verificar se o formulário é válido
// IMPORTANTE: Usar computed para garantir reatividade com useForm do Inertia
const isFormValid = computed(() => {
    try {
        // Acessar valores do formulário diretamente para garantir reatividade
        const formAmount = withdrawalForm.amount;
        const formPixKey = withdrawalForm.pix_key;
        const formPixKeyType = withdrawalForm.pix_key_type;
        
        // Converter valor para número (MoneyInput pode retornar string ou número)
        let amount = formAmount;
        if (amount === null || amount === undefined || amount === '') {
            amount = 0;
        } else if (typeof amount === 'string') {
            // Se for string, remover formatação e converter
            // Remover tudo exceto números, vírgula e ponto
            const cleaned = amount.toString().replace(/[^\d,.-]/g, '').replace(',', '.');
            amount = parseFloat(cleaned) || 0;
        } else {
            amount = parseFloat(amount) || 0;
        }
        
        // Verificar se há valor válido
        if (amount <= 0 || isNaN(amount)) {
            console.log('Validação falhou: amount inválido', { amount, formAmount, type: typeof formAmount });
            return false;
        }
        
        // Verificar se o valor está dentro dos limites (usar comparação numérica precisa)
        const minWithdraw = parseFloat(props.minWithdraw) || 0;
        const availableBalance = parseFloat(props.availableBalance) || 0;
        
        // IMPORTANTE: Não permitir saque maior que o saldo disponível
        // Permitir saque igual ou menor que o saldo disponível
        // Usar comparação arredondada para evitar problemas de ponto flutuante
        const amountRounded = Math.round(amount * 100) / 100;
        const availableBalanceRounded = Math.round(availableBalance * 100) / 100;
        
        if (amountRounded > availableBalanceRounded) {
            console.log('Validação falhou: amount > availableBalance', { 
                amount, 
                amountRounded, 
                availableBalance, 
                availableBalanceRounded 
            });
            return false;
        }
        
        // Verificar valor mínimo configurado (R$ 10,00 padrão)
        // O valor mínimo garante que após a taxa, reste pelo menos R$ 5,00 líquidos (exigido pela FullPix)
        const minWithdrawRounded = Math.round(minWithdraw * 100) / 100;
        if (amountRounded < minWithdrawRounded) {
            console.log('Validação falhou: amount < minWithdraw', { 
                amount, 
                amountRounded, 
                minWithdraw, 
                minWithdrawRounded 
            });
            return false;
        }
        
        // Verificar se o valor líquido é >= R$ 5,00 (mínimo exigido pela FullPix)
        const netAmount = parseFloat((calculatedWithdrawal.value.netAmount || 0).toFixed(2));
        const netAmountRounded = Math.round(netAmount * 100) / 100;
        const minNetAmount = 5.00;
        if (netAmountRounded < minNetAmount || isNaN(netAmount) || netAmountRounded <= 0) {
            console.log('Validação falhou: netAmount < minNetAmount', { 
                netAmount, 
                netAmountRounded, 
                minNetAmount 
            });
            return false;
        }
        
        // Verificar se há chave PIX
        const pixKey = (formPixKey || '').trim();
        if (!pixKey || pixKey === '') {
            console.log('Validação falhou: pixKey vazio', { pixKey });
            return false;
        }
        
        // Verificar se a chave PIX é válida (usar valores locais para garantir reatividade)
        const type = formPixKeyType;
        let pixKeyValid = false;
        
        if (type === 'EMAIL') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            pixKeyValid = emailRegex.test(pixKey) && pixKey.length >= 5 && pixKey.length <= 255;
        } else if (type === 'CPF') {
            const cpf = pixKey.replace(/\D/g, '');
            pixKeyValid = cpf.length === 11;
        } else if (type === 'CNPJ') {
            const cnpj = pixKey.replace(/\D/g, '');
            pixKeyValid = cnpj.length === 14;
        } else if (type === 'PHONE') {
            const phone = pixKey.replace(/\D/g, '');
            pixKeyValid = phone.length >= 10 && phone.length <= 11;
        } else if (type === 'EVP') {
            pixKeyValid = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(pixKey);
        }
        
        if (!pixKeyValid) {
            console.log('Validação falhou: pixKey inválido', { type, pixKey, pixKeyValid });
            return false;
        }
        
        // Todas as validações passaram
        return true;
    } catch (error) {
        console.error('Erro na validação do formulário:', error);
        return false;
    }
});

// Computed para verificar se o valor excede o saldo disponível (para exibição no template)
const exceedsAvailableBalance = computed(() => {
    let amount = withdrawalForm.amount;
    if (amount === null || amount === undefined || amount === '') {
        return false;
    } else if (typeof amount === 'string') {
        const cleaned = amount.toString().replace(/[^\d,.-]/g, '').replace(',', '.');
        amount = parseFloat(cleaned) || 0;
    } else {
        amount = parseFloat(amount) || 0;
    }
    
    if (amount <= 0 || isNaN(amount)) {
        return false;
    }
    
    const amountRounded = Math.round(amount * 100) / 100;
    const availableBalanceRounded = Math.round(props.availableBalance * 100) / 100;
    
    return amountRounded > availableBalanceRounded;
});

// Formatar horário para HH:mm (24h) - Horário de Brasília (UTC-3)
const formatTime = (date = new Date()) => {
    // Obter horário de Brasília (UTC-3)
    const formatter = new Intl.DateTimeFormat('pt-BR', {
        timeZone: 'America/Sao_Paulo',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false, // Formato 24h
    });
    
    const parts = formatter.formatToParts(date);
    const hours = parts.find(p => p.type === 'hour')?.value.padStart(2, '0') || '00';
    const minutes = parts.find(p => p.type === 'minute')?.value.padStart(2, '0') || '00';
    
    return `${hours}:${minutes}`;
};

// Criar saque
const createWithdrawal = async () => {
    // IMPORTANTE: Capturar o valor ANTES de qualquer validação
    // O MoneyInput pode retornar string formatada ou número
    // Usar o valor diretamente do formulário, mas garantir que seja um número válido
    let amount = withdrawalForm.amount;
    
    // Debug: verificar o valor original
    console.log('Valor original do formulário:', { 
        amount, 
        type: typeof amount, 
        formData: withdrawalForm.data() 
    });
    
    if (typeof amount === 'string') {
        // Se for string, remover formatação e converter
        const cleaned = amount.toString().replace(/[^\d,.-]/g, '').replace(',', '.');
        amount = parseFloat(cleaned);
    } else if (amount === null || amount === undefined || amount === '') {
        amount = 0;
    } else {
        amount = parseFloat(amount) || 0;
    }
    
    // Debug: verificar o valor convertido
    console.log('Valor convertido:', { amount, isNaN: isNaN(amount) });
    
    // Validar se há valor válido ANTES de continuar
    if (amount <= 0 || isNaN(amount)) {
        console.error('Valor inválido detectado:', { 
            original: withdrawalForm.amount, 
            converted: amount,
            formData: withdrawalForm.data()
        });
        toast({
            title: 'Valor inválido',
            description: 'Por favor, informe um valor válido para o saque. O valor informado foi: ' + (withdrawalForm.amount || 'vazio'),
            variant: 'destructive',
        });
        return;
    }
    
    // Validações básicas - não validar minWithdraw, apenas taxa + R$ 0,01

    // IMPORTANTE: Não permitir saque maior que o saldo disponível (usar comparação arredondada)
    const amountRounded = Math.round(amount * 100) / 100;
    const availableBalanceRounded = Math.round(props.availableBalance * 100) / 100;
    
    if (amountRounded > availableBalanceRounded) {
        toast({
            title: 'Saldo insuficiente',
            description: `Você não possui saldo suficiente. Saldo disponível: ${formatCurrency(props.availableBalance)}.`,
            variant: 'destructive',
        });
        return;
    }

    if (!withdrawalForm.pix_key || !validatePixKey()) {
        toast({
            title: 'Chave PIX inválida',
            description: 'Verifique o tipo e o valor da chave PIX informada.',
            variant: 'destructive',
        });
        return;
    }

    // Validar valor líquido mínimo (R$ 5,00)
    const minValidation = validateMinNetAmount();
    if (!minValidation.valid) {
        toast({
            title: 'Valor líquido insuficiente',
            description: minValidation.message,
            variant: 'destructive',
        });
        return;
    }

    loadingWithdrawal.value = true;

    try {
        // Obter CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // IMPORTANTE: Capturar o valor diretamente do formulário ANTES de qualquer processamento
        // O MoneyInput pode retornar o valor como string formatada ou número
        let amountToSend = withdrawalForm.amount;
        
        // Se for string, pode estar formatada (ex: "7,72" ou "7.72")
        if (typeof amountToSend === 'string') {
            // Remover formatação e converter
            const cleaned = amountToSend.toString().replace(/[^\d,.-]/g, '').replace(',', '.');
            amountToSend = parseFloat(cleaned);
        } else if (amountToSend === null || amountToSend === undefined || amountToSend === '') {
            amountToSend = 0;
        } else {
            amountToSend = parseFloat(amountToSend) || 0;
        }
        
        // Validar se o valor é válido antes de enviar
        if (amountToSend <= 0 || isNaN(amountToSend)) {
            toast({
                title: 'Valor inválido',
                description: 'Por favor, informe um valor válido para o saque.',
                variant: 'destructive',
            });
            loadingWithdrawal.value = false;
            return;
        }
        
        // Usar axios para capturar resposta JSON completa
        const response = await axios.post(route('withdrawal.create'), {
            amount: amountToSend,
            pix_key_type: withdrawalForm.pix_key_type,
            pix_key: (withdrawalForm.pix_key || '').trim(),
            pix_key_description: withdrawalForm.pix_key_description || '',
        }, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        if (response.data.status === 'success') {
            // Se foi confirmado automaticamente, exibir modal de confirmação
            if (response.data.confirmed && response.data.confirmed_message) {
                withdrawalConfirmation.value = {
                    net_amount: response.data.net_amount,
                    amount: response.data.amount,
                    fee: response.data.fee,
                    time: formatTime(),
                };
                showConfirmationModal.value = true;
                showWithdrawalModal.value = false;
            } else {
                toast({
                    title: 'Saque solicitado!',
                    description: response.data.message || 'Seu saque foi solicitado com sucesso e está sendo processado.',
                });
                showWithdrawalModal.value = false;
            }
            
            resetModal();
            
            // Recarregar dados financeiros
            router.reload({ only: ['withdrawals', 'saldoDisponivel', 'extratoData'] });
        } else {
            console.error('Resposta com status diferente de success:', response.data);
            toast({
                title: 'Erro ao solicitar saque',
                description: response.data.message || 'Ocorreu um erro ao processar o saque.',
                variant: 'destructive',
            });
        }
    } catch (error) {
        console.error('Erro ao solicitar saque:', error);
        const errorMessage = error.response?.data?.message || error.message || 'Ocorreu um erro. Tente novamente.';
        toast({
            title: 'Erro ao solicitar saque',
            description: errorMessage,
            variant: 'destructive',
        });
    } finally {
        loadingWithdrawal.value = false;
    }
};

// Resetar modal
const resetModal = () => {
    withdrawalForm.reset();
    withdrawalForm.pix_key_type = 'CPF';
    withdrawalForm.pix_key = '';
    withdrawalForm.pix_key_description = '';
};

// Placeholder para chave PIX
const getPixKeyPlaceholder = (type) => {
    const placeholders = {
        'CPF': '000.000.000-00',
        'CNPJ': '00.000.000/0000-00',
        'EMAIL': 'exemplo@email.com',
        'PHONE': '(00) 00000-0000',
        'EVP': 'UUID da chave aleatória'
    };
    return placeholders[type] || 'Digite a chave PIX';
};
</script>

<template>
    <div>
        <Dialog v-model:open="showWithdrawalModal" @update:open="(open) => { if (!open) resetModal(); }">
            <DialogTrigger as-child>
                <slot name="trigger">
                    <Button variant="outline">
                        <PlusIcon class="h-4 w-4" />
                        Solicitar Saque
                    </Button>
                </slot>
            </DialogTrigger>
            <DialogContent class="sm:max-w-lg">
                <DialogHeader class="text-center">
                    <DialogTitle class="text-xl">Nova Solicitação de Saque</DialogTitle>
                    <DialogDescription class="text-base">
                        Transfira seu saldo para uma chave PIX
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-6">
                    <!-- Saldo Disponível -->
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4">
                        <Label class="text-sm text-muted-foreground">Saldo Disponível</Label>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ formatCurrency(availableBalance) }}
                        </p>
                    </div>

                    <!-- Valor do Saque (PRIMEIRO) -->
                    <div class="space-y-2">
                        <Label for="amount">Valor do Saque</Label>
                        <MoneyInput 
                            v-model="withdrawalForm.amount" 
                            label=""
                            :max="availableBalance"
                        />
                        <p v-if="withdrawalForm.amount > 0 && withdrawalForm.amount < minWithdraw" class="text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" />
                            Valor mínimo: {{ formatCurrency(minWithdraw) }}
                        </p>
                        <p v-if="withdrawalForm.amount > 0 && exceedsAvailableBalance" class="text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" />
                            Valor excede o saldo disponível. Saldo: {{ formatCurrency(availableBalance) }}
                        </p>
                        <p v-if="withdrawalForm.amount > 0 && calculatedWithdrawal.netAmount < 5.00" class="text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" />
                            Valor líquido mínimo: R$ 5,00 (valor necessário: {{ formatCurrency(5.00 + calculatedWithdrawal.fee) }})
                        </p>
                    </div>

                    <!-- Resumo de Taxas e Valor Líquido (SEGUNDO) -->
                    <div v-if="withdrawalForm.amount > 0" class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 space-y-3 border border-blue-200 dark:border-blue-800">
                        <div class="flex justify-between items-center">
                            <Label class="text-sm text-muted-foreground">Valor solicitado</Label>
                            <span class="text-sm font-semibold">{{ formatCurrency(withdrawalForm.amount) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <Label class="text-sm text-red-600 dark:text-red-400">Taxa</Label>
                            <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                - {{ formatCurrency(calculatedWithdrawal.fee) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-blue-200 dark:border-blue-800">
                            <Label class="text-sm font-semibold">Você receberá</Label>
                            <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                {{ formatCurrency(calculatedWithdrawal.netAmount) }}
                            </span>
                        </div>
                    </div>

                    <!-- Chave PIX de Destino (TERCEIRO) -->
                    <div class="space-y-3">
                        <Label class="text-base font-semibold">Chave PIX de Destino</Label>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Tipo da Chave -->
                            <div class="space-y-2">
                                <Label for="pix_key_type" class="text-xs text-muted-foreground">Tipo</Label>
                                <Select v-model="withdrawalForm.pix_key_type" @update:model-value="(value) => { withdrawalForm.pix_key_type = value; }">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Tipo" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="CPF">CPF</SelectItem>
                                        <SelectItem value="CNPJ">CNPJ</SelectItem>
                                        <SelectItem value="EMAIL">E-mail</SelectItem>
                                        <SelectItem value="PHONE">Telefone</SelectItem>
                                        <SelectItem value="EVP">Aleatória</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Chave PIX -->
                            <div class="space-y-2">
                                <Label for="pix_key" class="text-xs text-muted-foreground">Chave PIX</Label>
                                <Input 
                                    id="pix_key"
                                    v-model="withdrawalForm.pix_key" 
                                    :placeholder="getPixKeyPlaceholder(withdrawalForm.pix_key_type)"
                                    class="font-mono text-xs"
                                />
                            </div>
                        </div>

                        <!-- Descrição (Opcional) -->
                        <div class="space-y-2">
                            <Label for="pix_key_description" class="text-xs text-muted-foreground">Descrição (Opcional)</Label>
                            <Input 
                                id="pix_key_description"
                                v-model="withdrawalForm.pix_key_description" 
                                placeholder="Ex: Nubank"
                            />
                        </div>
                    </div>

                    <!-- Botão Solicitar Saque -->
                    <Button 
                        @click="createWithdrawal" 
                        :disabled="loadingWithdrawal || !isFormValid" 
                        type="submit"
                        class="w-full"
                        size="lg"
                    >
                        <CheckIcon v-if="!loadingWithdrawal" class="h-4 w-4 mr-2" />
                        <Loader2Icon v-else class="h-4 w-4 mr-2 animate-spin" />
                        Solicitar Saque
                    </Button>
                    
                </div>

                <DialogFooter class="sm:justify-start">
                    <DialogClose as-child>
                        <Button :disabled="loadingWithdrawal" type="button" variant="secondary">
                            Cancelar
                        </Button>
                    </DialogClose>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Modal de Confirmação de Saque -->
        <Dialog v-model:open="showConfirmationModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader class="text-center">
                    <DialogTitle class="text-xl flex items-center justify-center gap-2">
                        <CheckIcon class="h-6 w-6 text-green-600 dark:text-green-400" />
                        Saque Confirmado!
                    </DialogTitle>
                    <DialogDescription class="text-base pt-2">
                        Seu saque de <strong>{{ formatCurrency(withdrawalConfirmation?.net_amount || 0) }}</strong> foi processado com sucesso.
                        <br />
                        O valor cairá instantaneamente ou em alguns minutos, dependendo da disponibilidade bancária.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="withdrawalConfirmation" class="space-y-4 pt-4">
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <Label class="text-sm text-muted-foreground">Valor solicitado</Label>
                            <span class="text-sm font-semibold">{{ formatCurrency(withdrawalConfirmation.amount) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <Label class="text-sm text-muted-foreground">Taxa aplicada</Label>
                            <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                - {{ formatCurrency(withdrawalConfirmation.fee) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                            <Label class="text-sm font-semibold">Valor líquido</Label>
                            <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                {{ formatCurrency(withdrawalConfirmation.net_amount) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                            <Label class="text-sm text-muted-foreground">Data/Hora</Label>
                            <span class="text-sm font-semibold">{{ withdrawalConfirmation.time }}</span>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button 
                        @click="showConfirmationModal = false; withdrawalConfirmation = null" 
                        class="w-full"
                        size="lg"
                    >
                        Fechar
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
