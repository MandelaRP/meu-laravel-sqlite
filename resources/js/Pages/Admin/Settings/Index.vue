<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { useToast } from '@/components/ui/toast/use-toast';
import Toaster from '@/components/ui/toast/Toaster.vue';
import { 
    Settings, 
    Wallet, 
    Percent, 
    CreditCard, 
    Coins, 
    Save,
    CircleDollarSign,
    TrendingUp,
    Shield
} from 'lucide-vue-next';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Configurações',
        href: '/admin/settings',
    },
];

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({
            gateway_pix_percentage: 0,
            gateway_pix_fixed: 1.00,
            payment_method_pix: true,
            payment_method_credit_card: true,
            payment_method_boleto: true
        })
    }
});

const { toast } = useToast();

// Dados das configurações
const settings = ref({
    transferMode: props.settings?.transfer_mode === 'automatico' ? 'Saque Automático' : (props.settings?.transfer_mode === 'manual' ? 'Enviar para Análise' : 'Enviar para Análise'),
    minWithdraw: String(props.settings?.min_withdraw ?? 10.00).replace('.', ','),
    fixedWithdrawFee: String(props.settings?.fixed_withdraw_fee ?? 5.00).replace('.', ','),
    percentWithdrawFee: String(props.settings?.percent_withdraw_fee ?? 0).replace('.', ','),
    pixFee: '2,99',
    gatewayFeePercentage: '2,99',
    gatewayPixPercentage: String(props.settings?.gateway_pix_percentage ?? 0),
    gatewayPixFixed: String(props.settings?.gateway_pix_fixed ?? 1.00),
    reserveEnabled: false,
    reservePercent: '0',
    reserveMax: '1000000,00',
    methods: {
        pix: props.settings?.payment_method_pix ?? true,
        card: props.settings?.payment_method_credit_card ?? true,
        boleto: props.settings?.payment_method_boleto ?? true
    }
});

const formatCurrency = (value) => {
    if (!value) return '';
    const numValue = parseFloat(value.replace(',', '.'));
    if (isNaN(numValue)) return value;
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(numValue);
};

const formatPercent = (value) => {
    if (!value) return '';
    return `${value}%`;
};

const handleCurrencyInput = (field, value) => {
    // Remove tudo exceto números e vírgula
    const cleaned = value.replace(/[^\d,]/g, '');
    // Substitui vírgula por ponto temporariamente para cálculo
    const numValue = parseFloat(cleaned.replace(',', '.'));
    if (!isNaN(numValue)) {
        settings.value[field] = cleaned;
    } else if (cleaned === '') {
        settings.value[field] = '';
    }
};

const handlePercentInput = (field, value) => {
    // Remove tudo exceto números e vírgula
    const cleaned = value.replace(/[^\d,]/g, '');
    settings.value[field] = cleaned;
};

const saveSettings = async () => {
    try {
        const formData = {
            gatewayFeePercentage: parseFloat(settings.value.gatewayFeePercentage.replace(',', '.')) || 0,
            gatewayPixPercentage: parseFloat(settings.value.gatewayPixPercentage?.replace(',', '.') || '0') || 0,
            gatewayPixFixed: parseFloat(settings.value.gatewayPixFixed?.replace(',', '.') || '0') || 0,
            payment_method_pix: settings.value.methods.pix,
            payment_method_credit_card: settings.value.methods.card,
            payment_method_boleto: settings.value.methods.boleto,
            pixFee: parseFloat(settings.value.pixFee.replace(',', '.')) || 0,
            minWithdraw: parseFloat(settings.value.minWithdraw.replace(',', '.')) || 0,
            fixedWithdrawFee: parseFloat(settings.value.fixedWithdrawFee.replace(',', '.')) || 0,
            percentWithdrawFee: parseFloat(settings.value.percentWithdrawFee.replace(',', '.')) || 0,
            reserveEnabled: settings.value.reserveEnabled,
            reservePercent: parseFloat(settings.value.reservePercent.replace(',', '.')) || 0,
            reserveMax: parseFloat(settings.value.reserveMax.replace(',', '.').replace(/\./g, '')) || 0,
            transferMode: settings.value.transferMode === 'Saque Automático' ? 'automatico' : 'manual',
            methods: settings.value.methods,
        };

        const response = await axios.post(route('admin.settings.update'), formData);
        
        toast({
            title: 'Configurações salvas com sucesso!',
            description: 'Todas as alterações foram aplicadas ao sistema.',
        });
    } catch (error) {
        console.error('Erro ao salvar configurações:', error);
        toast({
            title: 'Erro ao salvar configurações',
            description: error.response?.data?.message || 'Ocorreu um erro ao salvar. Tente novamente.',
            variant: 'destructive',
        });
    }
};
</script>

<template>
    <Head title="Configurações do Sistema" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mt-2 sm:mt-4 flex h-full flex-1 flex-col gap-6 p-2 sm:p-4 w-full mx-auto">
            <!-- Cabeçalho -->
            <div class="space-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground/80 mb-1">Bem-vindo, Admin</p>
                        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight">Configurações do Sistema</h2>
                        <p class="text-sm sm:text-base text-muted-foreground/80 mt-1">
                            Configure as taxas e parâmetros do sistema.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Grid de Cards - Layout Horizontal em telas médias/grandes -->
            <div class="flex flex-col md:flex-row md:flex-wrap gap-4 md:gap-6 max-w-7xl mx-auto">
                <!-- Card - Transferências e Saques -->
                <Card class="border-border bg-card rounded-2xl shadow-md flex-1 md:min-w-[calc(50%-12px)] lg:min-w-[calc(50%-18px)]">
                    <CardHeader class="pb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500">
                                <Wallet class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <CardTitle class="text-base sm:text-lg">Transferências e Saques</CardTitle>
                                <CardDescription class="text-sm mt-1">
                                    Configure os parâmetros de transferência de saldo.
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="transferMode" class="text-sm font-medium">Modo de Transferência</Label>
                            <Select v-model="settings.transferMode">
                                <SelectTrigger id="transferMode" class="bg-background border-border">
                                    <SelectValue placeholder="Selecione o modo" />
                                </SelectTrigger>
                                <SelectContent class="bg-popover border-border">
                                    <SelectItem value="Saque Automático" class="text-popover-foreground">
                                        Saque Automático
                                    </SelectItem>
                                    <SelectItem value="Enviar para Análise" class="text-popover-foreground">
                                        Enviar para Análise
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="minWithdraw" class="text-sm font-medium">Valor Mínimo para Saque</Label>
                            <Input
                                id="minWithdraw"
                                v-model="settings.minWithdraw"
                                placeholder="R$ 10,00"
                                class="bg-background border-border"
                                @input="(e) => handleCurrencyInput('minWithdraw', e.target.value)"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="fixedWithdrawFee" class="text-sm font-medium">Taxa Fixa de Saque</Label>
                            <Input
                                id="fixedWithdrawFee"
                                v-model="settings.fixedWithdrawFee"
                                placeholder="R$ 5,00"
                                class="bg-background border-border"
                                @input="(e) => handleCurrencyInput('fixedWithdrawFee', e.target.value)"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="percentWithdrawFee" class="text-sm font-medium">Taxa Percentual de Saque</Label>
                            <Input
                                id="percentWithdrawFee"
                                v-model="settings.percentWithdrawFee"
                                placeholder="0%"
                                class="bg-background border-border"
                                @input="(e) => handlePercentInput('percentWithdrawFee', e.target.value)"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Card - Taxas PIX e Gateway -->
                <Card class="border-border bg-card rounded-2xl shadow-md flex-1 md:min-w-[calc(50%-12px)] lg:min-w-[calc(50%-18px)]">
                    <CardHeader class="pb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-500">
                                <Percent class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <CardTitle class="text-base sm:text-lg">Taxas PIX e Gateway</CardTitle>
                                <CardDescription class="text-sm mt-1">
                                    Configure as taxas para transações PIX e comissão da gateway.
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="gatewayPixFixed" class="text-sm font-medium">Taxa Fixa (R$)</Label>
                            <Input
                                id="gatewayPixFixed"
                                v-model="settings.gatewayPixFixed"
                                placeholder="1,00"
                                class="bg-background border-border"
                                @input="(e) => handleCurrencyInput('gatewayPixFixed', e.target.value)"
                            />
                            <p class="text-xs text-muted-foreground">
                                A taxa fixa será sempre descontada do valor total de cada PIX.
                            </p>
                        </div>
                        <div class="space-y-2">
                            <Label for="gatewayPixPercentage" class="text-sm font-medium">Taxa Percentual (%)</Label>
                            <Input
                                id="gatewayPixPercentage"
                                v-model="settings.gatewayPixPercentage"
                                placeholder="2,99%"
                                class="bg-background border-border"
                                @input="(e) => handlePercentInput('gatewayPixPercentage', e.target.value)"
                            />
                            <p class="text-xs text-muted-foreground">
                                A taxa percentual é aplicada sobre o valor do PIX e somada à taxa fixa.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card - Reserva Financeira -->
                <Card class="border-border bg-card rounded-2xl shadow-md flex-1 md:min-w-[calc(50%-12px)] lg:min-w-[calc(50%-18px)]">
                    <CardHeader class="pb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-500">
                                <Shield class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <CardTitle class="text-base sm:text-lg">Reserva Financeira</CardTitle>
                                <CardDescription class="text-sm mt-1">
                                    Configure o percentual de reserva para novos usuários.
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center justify-between">
                            <Label for="reserveEnabled" class="text-sm font-medium">Ativar Reserva</Label>
                            <Switch
                                id="reserveEnabled"
                                v-model:checked="settings.reserveEnabled"
                                class="data-[state=checked]:bg-primary"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="reservePercent" class="text-sm font-medium">Percentual de Reserva</Label>
                            <Input
                                id="reservePercent"
                                v-model="settings.reservePercent"
                                placeholder="0%"
                                class="bg-background border-border"
                                :disabled="!settings.reserveEnabled"
                                @input="(e) => handlePercentInput('reservePercent', e.target.value)"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="reserveMax" class="text-sm font-medium">Valor Máximo de Reserva</Label>
                            <Input
                                id="reserveMax"
                                v-model="settings.reserveMax"
                                placeholder="R$ 1.000.000,00"
                                class="bg-background border-border"
                                :disabled="!settings.reserveEnabled"
                                @input="(e) => handleCurrencyInput('reserveMax', e.target.value)"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Card - Métodos de Pagamento -->
                <Card class="border-border bg-card rounded-2xl shadow-md flex-1 md:min-w-[calc(50%-12px)] lg:min-w-[calc(50%-18px)]">
                    <CardHeader class="pb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-500">
                                <CreditCard class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <CardTitle class="text-base sm:text-lg">Métodos de Pagamento</CardTitle>
                                <CardDescription class="text-sm mt-1">
                                    Ative ou desative métodos disponíveis para o sistema.
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center justify-between">
                            <Label for="pixMethod" class="text-sm font-medium">PIX</Label>
                            <Switch
                                id="pixMethod"
                                v-model:checked="settings.methods.pix"
                                class="data-[state=checked]:bg-primary"
                            />
                        </div>
                        <div class="flex items-center justify-between">
                            <Label for="cardMethod" class="text-sm font-medium">Cartão de Crédito</Label>
                            <Switch
                                id="cardMethod"
                                v-model:checked="settings.methods.card"
                                class="data-[state=checked]:bg-primary"
                            />
                        </div>
                        <div class="flex items-center justify-between">
                            <Label for="boletoMethod" class="text-sm font-medium">Boleto Bancário</Label>
                            <Switch
                                id="boletoMethod"
                                v-model:checked="settings.methods.boleto"
                                class="data-[state=checked]:bg-primary"
                            />
                        </div>
                        <div class="p-3 bg-muted rounded-lg mt-4">
                            <p class="text-xs text-muted-foreground">
                                <strong>Nota:</strong> Ao alterar um método, o sistema refletirá imediatamente nas opções de pagamento disponíveis aos usuários.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Botão de Salvar -->
            <div class="flex justify-center pt-6 max-w-4xl mx-auto">
                <Button 
                    @click="saveSettings"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 px-8 py-6 text-base"
                    size="lg"
                >
                    <Save class="h-5 w-5 mr-2" />
                    Salvar Configurações
                </Button>
            </div>
        </div>
        <Toaster />
    </AppLayout>
</template>

