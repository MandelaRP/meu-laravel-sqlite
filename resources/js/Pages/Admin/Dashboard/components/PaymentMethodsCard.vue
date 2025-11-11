<script setup>
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { CreditCard, QrCode, FileText } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { route } from 'ziggy-js';

const props = defineProps({
    paymentMethods: {
        type: Object,
        default: () => ({
            pix: 0,
            credit_card: 0,
            boleto: 0
        })
    },
    paymentMethodsStatus: {
        type: Object,
        default: () => ({
            pix: true,
            credit_card: true,
            boleto: true
        })
    }
});

const paymentMethods = ref({
    pix: props.paymentMethodsStatus?.pix ?? true,
    credit_card: props.paymentMethodsStatus?.credit_card ?? true,
    boleto: props.paymentMethodsStatus?.boleto ?? true
});

const methods = computed(() => [
    {
        key: 'pix',
        name: 'PIX',
        icon: QrCode,
        percentage: props.paymentMethods.pix || 0,
        enabled: paymentMethods.value.pix
    },
    {
        key: 'credit_card',
        name: 'Cartão de Crédito',
        icon: CreditCard,
        percentage: props.paymentMethods.credit_card || 0,
        enabled: paymentMethods.value.credit_card
    },
    {
        key: 'boleto',
        name: 'Boleto',
        icon: FileText,
        percentage: props.paymentMethods.boleto || 0,
        enabled: paymentMethods.value.boleto
    }
]);

const toggleMethod = async (methodKey) => {
    const method = methods.value.find(m => m.key === methodKey);
    if (!method) return;
    
    const newValue = !method.enabled;
    paymentMethods.value[methodKey] = newValue;
    
    try {
        await axios.post(route('admin.settings.update'), {
            [`payment_method_${methodKey}`]: newValue
        });
        
        // Recarregar página para refletir mudanças
        router.reload({ only: ['financialData', 'paymentMethodsStatus'] });
    } catch (error) {
        console.error('Erro ao atualizar método de pagamento:', error);
        // Reverter mudança em caso de erro
        paymentMethods.value[methodKey] = !newValue;
        alert('Erro ao atualizar método de pagamento. Tente novamente.');
    }
};
</script>

<template>
    <Card class="w-full border-border bg-card">
        <CardHeader>
            <CardTitle class="text-base sm:text-lg">📊 Métodos de Pagamento</CardTitle>
            <CardDescription>
                Ative ou desative métodos de pagamento e visualize a distribuição percentual
            </CardDescription>
        </CardHeader>
        <CardContent>
            <div class="space-y-4">
                <div
                    v-for="method in methods"
                    :key="method.key"
                    class="flex items-center justify-between p-4 rounded-lg border bg-card"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                            <component :is="method.icon" class="h-5 w-5 text-white" />
                        </div>
                        <div>
                            <Label class="text-sm font-medium">{{ method.name }}</Label>
                            <p class="text-xs text-muted-foreground">{{ method.percentage }}% das vendas</p>
                        </div>
                    </div>
                    <Switch
                        :checked="method.enabled"
                        @update:checked="toggleMethod(method.key)"
                    />
                </div>
            </div>
        </CardContent>
    </Card>
</template>

