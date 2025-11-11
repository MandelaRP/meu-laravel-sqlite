<script setup>
import CountdownTimer from './components/CountdownTimer.vue';
import OrderBump from '@/pages/Checkout/components/OrderBump.vue';
import Form from '@/pages/Checkout/components/Form.vue';
import SubmitButton from '@/components/checkout/SubmitButton.vue';
import PaymentMethods from '@/pages/Checkout/components/PaymentMethods.vue';
import OrderSummary from '@/pages/Checkout/components/OrderSummary.vue';
import { computed, ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import Banner from './components/Banner.vue';

const props = defineProps({
    checkout: Object,
});

const formRef = ref(null);
const isSubmitting = ref(false);
const submitSuccess = ref(false);
const submitError = ref('');
// Pré-selecionar o primeiro método de pagamento habilitado
const enabledMethods = computed(() => {
    return props.checkout?.payment_methods?.filter(m => m.enabled === true || m.enabled === "1") || [];
});
const selectedPaymentMethod = ref('');

// Inicializar método selecionado ao montar
onMounted(() => {
    if (enabledMethods.value.length > 0 && !selectedPaymentMethod.value) {
        selectedPaymentMethod.value = enabledMethods.value[0].name;
    }
});

const selectedOrderBumps = ref(new Set());

const CountdownEnabled = computed(() => props.checkout?.countdown_enabled || false);

// Computed para verificar se o formulário é válido
const isFormValid = computed(() => {
    if (!formRef.value) return false;
    return formRef.value.checkFormValidity ? formRef.value.checkFormValidity() : false;
});

// Computed para verificar se há dados no formulário
const hasFormData = computed(() => {
    if (!formRef.value) return false;
    const formData = formRef.value.formData;
    return Object.values(formData).some(value => value && value.trim() !== '');
});

// Função para lidar com o envio do formulário
// const handleFormSubmitted = (data) => {
//   console.log('Formulário enviado:', data)
//   // data.formData - dados do formulário
//   // data.isValid - se a validação passou
//   // data.timestamp - quando foi enviado
//   // data.error - mensagem de erro (se houver)
// }

// Função para lidar com as alterações no formulário
// const handleFormDataChanged = (data) => {
//   console.log('Dados do formulário mudaram:', data)
//   // data.formData - dados atuais
//   // data.hasErrors - se há erros de validação
//   // data.timestamp - quando mudou
// }

// Função para lidar com seleção de método de pagamento
const handlePaymentMethodSelected = (methodName) => {
    selectedPaymentMethod.value = methodName;
    console.log('Método de pagamento selecionado:', methodName);
}

// Função para lidar com toggle dos order bumps
const handleOrderBumpToggle = (data) => {
    const { orderBumpId, checked } = data;

    if (checked) {
        selectedOrderBumps.value.add(orderBumpId);
    } else {
        selectedOrderBumps.value.delete(orderBumpId);
    }

    console.log('Order bump toggle:', { orderBumpId, checked, selectedOrderBumps: Array.from(selectedOrderBumps.value) });
}

// Função para enviar o formulário
const submitForm = async () => {
    // Validar formulário
    if (!formRef.value || !isFormValid.value) {
        submitError.value = 'Por favor, preencha todos os campos obrigatórios.';
        return;
    }

    if (!selectedPaymentMethod.value) {
        submitError.value = 'Por favor, selecione uma forma de pagamento.';
        return;
    }

    // Apenas PIX por enquanto
    if (selectedPaymentMethod.value !== 'pix') {
        submitError.value = 'Por enquanto, apenas pagamento via PIX está disponível.';
        return;
    }

    isSubmitting.value = true;
    submitError.value = '';
    submitSuccess.value = false;

    try {
        // Preparar dados para envio
        const formData = formRef.value.formData
        const orderBumpIds = Array.from(selectedOrderBumps.value)

        // Enviar para processar pagamento
        router.post(route('checkout.process-payment', props.checkout.id), {
            ...formData,
            payment_method: selectedPaymentMethod.value,
            order_bump_ids: orderBumpIds,
        }, {
            preserveScroll: false,
            onError: (errors) => {
                submitError.value = errors.error || Object.values(errors).flat().join(', ') || 'Erro ao processar pagamento. Tente novamente.'
                isSubmitting.value = false
            },
            onFinish: () => {
                isSubmitting.value = false
            }
        })

    } catch (error) {
        console.error('Erro ao enviar formulário:', error);
        submitError.value = 'Erro ao enviar formulário. Tente novamente.';
        isSubmitting.value = false;
    }
}

</script>

<template>
    <div class="max-w-2xl mx-auto pt-8">
        <!-- Banner -->
        <Banner v-if="checkout.banner" :banner="checkout.banner" :product-name="checkout.product?.name || ''" />
        
        <CountdownTimer v-if="CountdownEnabled" :checkout="props.checkout" />
        <!-- Espaçamento fixo e independente do contador - sempre mt-8 -->
        <div class="mt-8">
            <Card class="w-full max-w-2xl mx-auto shadow-lg !bg-white !text-gray-900 border border-gray-300 dark:!bg-white dark:!text-gray-900 rounded-2xl overflow-hidden">
            <CardHeader class="!bg-white !text-gray-900 pb-4">
                <CardTitle class="!text-gray-900 dark:!text-gray-900">
                    Complete seu pedido
                </CardTitle>
                <CardDescription class="!text-gray-600 dark:!text-gray-600">
                    Complete seu pedido para garantir a melhor experiência.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6 !bg-white !text-gray-900 dark:!bg-white dark:!text-gray-900 pt-0">
                <Form ref="formRef" :form="checkout.form_fields_config" :show-submit-button="false" />

                <!-- Métodos de Pagamento -->
                <PaymentMethods :payment-methods="checkout.payment_methods" :selected-method="selectedPaymentMethod"
                    @method-selected="handlePaymentMethodSelected" />
                <OrderBump v-for="orderBump in checkout.order_bumps" v-show="checkout.order_bump_enabled"
                    :key="orderBump.id" :orderBump="orderBump" :checkout="checkout"
                    @order-bump-toggle="handleOrderBumpToggle" />

                <!-- Resumo do Pedido -->
                <OrderSummary :checkout="checkout" :selected-payment-method="selectedPaymentMethod"
                    :form-data="formRef?.formData || {}" :selected-order-bumps="selectedOrderBumps" />

                <!-- Botão de Submit Componentizado -->
                <SubmitButton :is-submitting="isSubmitting" :is-valid="isFormValid" :has-data="hasFormData"
                    :submit-success="submitSuccess" :submit-error="submitError"
                    :primary-color="checkout.button_primary_color" :hover-color="checkout.button_hover_primary_color"
                    @click="submitForm" />
            </CardContent>
        </Card>
        </div>
    </div>
</template>
