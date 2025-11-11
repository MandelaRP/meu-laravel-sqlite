<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
    Loader2,
    ArrowRight,
    User,
    CreditCard,
    ShieldCheck,
    ShoppingBag,
    Check,
    MapPin,
    ChevronLeft,
    ChevronRight,
    CreditCard as CreditCardIcon,
    Receipt
} from 'lucide-vue-next';
import { paymentMethodColors, paymentMethods } from '@/components/PaymentMethodConfig.js';
import PaymentMethodOption from '@/components/PaymentMethodOption.vue';
import CountdownTimer from '@/components/CountdownTimer.vue';
import OrderBump from '@/pages/Checkout/components/OrderBump.vue';

// Mock data for demonstration
const props = defineProps({
    checkout: {
        type: Object,
        required: true
    }
});

// Debug: verificar cor de fundo
console.log('Checkout background color:', props.checkout.background_color);
console.log('Checkout dark mode:', props.checkout.dark_mode);
console.log('Step colors:', {
    active: props.checkout.step_active_color,
    completed: props.checkout.step_completed_color,
    inactive: props.checkout.step_inactive_color,
    text: props.checkout.step_text_color
});



// Função para gerar cores dinâmicas dos métodos de pagamento
const generatePaymentMethodColors = (method) => {
    // Obter a cor específica do método
    let iconBackgroundColor = props.checkout.payment_icon_background_color || '#eff6ff';
    let checkIconColor = '#ffffff'; // Cor padrão do ícone de check

    switch (method) {
        case 'pix':
            iconBackgroundColor = props.checkout.pix_icon_background_color || '#10b981';
            checkIconColor = props.checkout.pix_check_icon_background_color || '#ffffff';
            console.log('PIX check icon color in generatePaymentMethodColors:', checkIconColor);
            break;
        case 'credit_card':
            iconBackgroundColor = props.checkout.credit_card_icon_background_color || '#3b82f6';
            checkIconColor = props.checkout.credit_card_check_icon_background_color || '#ffffff';
            console.log('Credit card check icon color in generatePaymentMethodColors:', checkIconColor);
            break;
        case 'boleto':
            iconBackgroundColor = props.checkout.boleto_icon_background_color || '#f97316';
            checkIconColor = props.checkout.boleto_check_icon_background_color || '#ffffff';
            console.log('Boleto check icon color in generatePaymentMethodColors:', checkIconColor);
            break;
    }

    const baseColors = {
        pix: {
            primary: iconBackgroundColor,
            secondary: '#059669',
            background: '#ecfdf5'
        },
        credit_card: {
            primary: iconBackgroundColor,
            secondary: '#1d4ed8',
            background: '#eff6ff'
        },
        boleto: {
            primary: iconBackgroundColor,
            secondary: '#d97706',
            background: '#fffbeb'
        }
    };

    const colors = baseColors[method] || baseColors.credit_card;

    const result = {
        border: `border-[${colors.primary}] dark:border-[${colors.primary}]`,
        bg: `bg-[${colors.background}]/50 dark:bg-[${colors.primary}]/30`,
        shadow: `shadow-lg shadow-[${colors.primary}]/20 dark:shadow-[${colors.primary}]/30`,
        iconBg: iconBackgroundColor,
        checkMark: `bg-[${colors.primary}] dark:bg-[${colors.primary}]`,
        checkIconColor: checkIconColor,
        titleSelected: `text-[${colors.primary}] dark:text-[${colors.primary}]`,
        titleDefault: 'text-gray-300 dark:text-white',
        descriptionSelected: `text-[${colors.primary}] dark:text-[${colors.primary}]`,
        descriptionDefault: 'text-gray-400 dark:text-gray-300',
        radioSelected: `border-[${colors.primary}] bg-[${colors.primary}] dark:border-[${colors.primary}] dark:bg-[${colors.primary}]`,
        radioDefault: 'border-gray-300 dark:border-gray-500',
        badge: `bg-[${colors.background}] text-[${colors.primary}] dark:bg-[${colors.primary}] dark:text-white`,
        hoverBorder: `hover:border-[${colors.primary}] dark:hover:border-[${colors.primary}]`,
        hoverBg: `hover:bg-[${colors.background}]/30 dark:hover:bg-[${colors.primary}]/20`,
        defaultBorder: 'border-gray-200 dark:border-gray-600',
        defaultBg: 'dark:bg-gray-800'
    };

    return result;
};

// Cores personalizadas dos métodos de pagamento
const customPaymentMethodColors = computed(() => {
    const colors = {};
    paymentMethods.forEach(method => {
        colors[method.method] = generatePaymentMethodColors(method.method);
    });

    // Debug das cores geradas
    console.log('=== PAYMENT METHOD COLORS DEBUG ===');
    console.log('Generated colors:', colors);
    console.log('PIX colors:', colors.pix);
    console.log('Credit card colors:', colors.credit_card);
    console.log('Boleto colors:', colors.boleto);
    console.log('PIX check icon color:', colors.pix?.checkIconColor);
    console.log('Credit card check icon color:', colors.credit_card?.checkIconColor);
    console.log('Boleto check icon color:', colors.boleto?.checkIconColor);
    console.log('PIX check icon color type:', typeof colors.pix?.checkIconColor);
    console.log('Credit card check icon color type:', typeof colors.credit_card?.checkIconColor);
    console.log('Boleto check icon color type:', typeof colors.boleto?.checkIconColor);
    console.log('=== END PAYMENT METHOD COLORS DEBUG ===');

    return colors;
});

const customerData = ref({
    name: '',
    email: '',
    phone: '',
    cpf_cnpj: '',
    address: '',
    cep: '',
    city: '',
    state: '',
    neighborhood: '',
    number: '',
    complement: '',
    // Dados do cartão de crédito
    card_number: '',
    card_holder: '',
    card_expiry: '',
    card_cvv: '',
    card_installments: 1,
});

const paymentMethod = ref('pix');
const isProcessing = ref(false);
const isSearchingCep = ref(false);
const orderBumpsChecked = ref({});
const orderBumpAnimations = ref({});

// Step management
const currentStep = ref(3);

// Debug: verificar se as cores estão sendo aplicadas corretamente
console.log('=== DEBUG CHECKOUT COLORS ===');
console.log('Current step:', currentStep.value);
console.log('Step active color:', props.checkout.step_active_color);
console.log('Step completed color:', props.checkout.step_completed_color);
console.log('Step inactive color:', props.checkout.step_inactive_color);
console.log('Step text color:', props.checkout.step_text_color);
console.log('Background color:', props.checkout.background_color);
console.log('=== PAYMENT METHOD COLORS ===');
console.log('PIX color:', props.checkout.pix_icon_background_color);
console.log('Credit card color:', props.checkout.credit_card_icon_background_color);
console.log('Boleto color:', props.checkout.boleto_icon_background_color);
console.log('PIX check background color:', props.checkout.pix_check_icon_background_color);
console.log('Credit card check background color:', props.checkout.credit_card_check_icon_background_color);
console.log('Boleto check background color:', props.checkout.boleto_check_icon_background_color);
console.log('=== CHECK ICON COLORS DEBUG ===');
console.log('PIX check icon background color from props:', props.checkout.pix_check_icon_background_color);
console.log('Credit card check icon background color from props:', props.checkout.credit_card_check_icon_background_color);
console.log('Boleto check icon background color from props:', props.checkout.boleto_check_icon_background_color);
console.log('Type of PIX check color:', typeof props.checkout.pix_check_icon_background_color);
console.log('Type of Credit card check color:', typeof props.checkout.credit_card_check_icon_background_color);
console.log('Type of Boleto check color:', typeof props.checkout.boleto_check_icon_background_color);
console.log('Raw checkout object keys:', Object.keys(props.checkout));
console.log('=== END CHECK ICON COLORS DEBUG ===');
console.log('Enabled payment methods:', props.checkout.enabled_payment_methods);
console.log('Full checkout object:', props.checkout);
console.log('=== END DEBUG ===');

// Teste temporário - forçar cor laranja para debug
const debugActiveColor = '#ff772e';
const steps = computed(() => {
    const hasAddressFields = props.checkout.require_address ||
        props.checkout.require_cep ||
        props.checkout.require_city ||
        props.checkout.require_state ||
        props.checkout.require_neighborhood ||
        props.checkout.require_number ||
        props.checkout.require_complement;

    if (hasAddressFields) {
        return [
            { id: 1, title: 'Dados', icon: User, completed: false },
            { id: 2, title: 'Endereço', icon: MapPin, completed: false },
            { id: 3, title: 'Pagamento', icon: CreditCard, completed: false },
            { id: 4, title: 'Finalizar', icon: Check, completed: false }
        ];
    } else {
        return [
            { id: 1, title: 'Dados Pessoais', icon: User, completed: false },
            { id: 2, title: 'Pagamento', icon: CreditCard, completed: false },
            { id: 3, title: 'Finalizar', icon: Check, completed: false }
        ];
    }
});

const orderBumps = computed(() => {
    return props.checkout?.active_order_bumps || [];
});

const selectedOrderBumps = computed(() => {
    if (!orderBumps.value || !Array.isArray(orderBumps.value)) {
        return [];
    }

    return orderBumps.value.filter((orderBump: any) => {
        const checked = orderBumpsChecked.value as any;
        return orderBump && orderBump.id && checked && checked[orderBump.id] === true;
    });
});

const totalPrice = computed(() => {
    let total = props.checkout?.product?.price || 0;

    if (orderBumps.value && Array.isArray(orderBumps.value)) {
        orderBumps.value.forEach(orderBump => {
            if (orderBump && orderBump.id && orderBumpsChecked.value && orderBumpsChecked.value[orderBump.id]) {
                total += orderBump.price || 0;
            }
        });
    }

    return total;
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(price);
};

// Validação por step
const isStepValid = computed(() => {
    switch (currentStep.value) {
        case 1: // Dados Pessoais
            let personalValid = true;
            if (props.checkout.require_name && !customerData.value.name.trim()) {
                personalValid = false;
            }
            if (props.checkout.require_email && !customerData.value.email.trim()) {
                personalValid = false;
            }
            if (props.checkout.require_phone && !customerData.value.phone.trim()) {
                personalValid = false;
            }
            if (props.checkout.require_cpf_cnpj && !customerData.value.cpf_cnpj.trim()) {
                personalValid = false;
            }
            return personalValid;

        case 2: // Endereço (se existir) ou Pagamento
            const hasAddressFields = props.checkout.require_address ||
                props.checkout.require_cep ||
                props.checkout.require_city ||
                props.checkout.require_state ||
                props.checkout.require_neighborhood ||
                props.checkout.require_number ||
                props.checkout.require_complement;

            if (hasAddressFields) {
                let addressValid = true;
                if (props.checkout.require_address && !customerData.value.address.trim()) {
                    addressValid = false;
                }
                if (props.checkout.require_cep && !customerData.value.cep.trim()) {
                    addressValid = false;
                }
                if (props.checkout.require_city && !customerData.value.city.trim()) {
                    addressValid = false;
                }
                if (props.checkout.require_state && !customerData.value.state.trim()) {
                    addressValid = false;
                }
                if (props.checkout.require_neighborhood && !customerData.value.neighborhood.trim()) {
                    addressValid = false;
                }
                if (props.checkout.require_number && !customerData.value.number.trim()) {
                    addressValid = false;
                }
                return addressValid;
            } else {
                return true; // Sem campos de endereço, sempre válido
            }

        case 3: // Pagamento (se tem endereço) ou Finalizar (se não tem endereço)
            // Validação específica para cada método de pagamento
            if (paymentMethod.value === 'credit_card') {
                return customerData.value.card_number.trim() &&
                    customerData.value.card_holder.trim() &&
                    customerData.value.card_expiry.trim() &&
                    customerData.value.card_cvv.trim();
            }
            return true; // PIX e Boleto sempre válidos

        case 4: // Finalizar (apenas se tem endereço)
            return true; // Sempre válido para finalizar

        default:
            return false;
    }
});

const isFormValid = computed(() => {
    let isValid = true;

    // Campos obrigatórios baseados na configuração do checkout
    if (props.checkout.require_name && !customerData.value.name.trim()) {
        isValid = false;
    }

    if (props.checkout.require_email && !customerData.value.email.trim()) {
        isValid = false;
    }

    if (props.checkout.require_phone && !customerData.value.phone.trim()) {
        isValid = false;
    }

    if (props.checkout.require_cpf_cnpj && !customerData.value.cpf_cnpj.trim()) {
        isValid = false;
    }

    if (props.checkout.require_address && !customerData.value.address.trim()) {
        isValid = false;
    }

    if (props.checkout.require_cep && !customerData.value.cep.trim()) {
        isValid = false;
    }

    if (props.checkout.require_city && !customerData.value.city.trim()) {
        isValid = false;
    }

    if (props.checkout.require_state && !customerData.value.state.trim()) {
        isValid = false;
    }

    if (props.checkout.require_neighborhood && !customerData.value.neighborhood.trim()) {
        isValid = false;
    }

    if (props.checkout.require_number && !customerData.value.number.trim()) {
        isValid = false;
    }

    return isValid;
});

const getButtonClasses = computed(() => {
    const primaryColor = props.checkout.button_primary_color || '#10b981';
    const secondaryColor = props.checkout.button_secondary_color || '#059669';
    const hoverPrimaryColor = props.checkout.button_hover_primary_color || '#059669';
    const hoverSecondaryColor = props.checkout.button_hover_secondary_color || '#047857';

    return {
        background: `linear-gradient(to right, ${primaryColor}, ${secondaryColor})`,
        hoverBackground: `linear-gradient(to right, ${hoverPrimaryColor}, ${hoverSecondaryColor})`,
        style: `background: linear-gradient(to right, ${primaryColor}, ${secondaryColor}) !important;`,
        customStyle: {
            backgroundImage: `linear-gradient(to right, ${primaryColor}, ${secondaryColor})`,
        }
    };
});

// Função para buscar endereço pelo CEP
const searchAddressByCep = async (cep: string) => {
    if (!cep || cep.length < 8) return;

    // Remove caracteres não numéricos
    const cleanCep = cep.replace(/\D/g, '');

    if (cleanCep.length !== 8) return;

    isSearchingCep.value = true;

    try {
        const response = await fetch(`https://viacep.com.br/ws/${cleanCep}/json/`);
        const data = await response.json();

        if (!data.erro) {
            // Preenche automaticamente os campos de endereço
            customerData.value.address = data.logradouro || '';
            customerData.value.neighborhood = data.bairro || '';
            customerData.value.city = data.localidade || '';
            customerData.value.state = data.uf || '';

            // Se o checkout não requer complemento, mas temos complemento da API, podemos mostrar uma mensagem
            if (data.complemento && !props.checkout.require_complement) {
                console.log('CEP encontrado com complemento:', data.complemento);
            }
        } else {
            console.log('CEP não encontrado');
        }
    } catch (error) {
        console.error('Erro ao buscar CEP:', error);
    } finally {
        isSearchingCep.value = false;
    }
};

// Função para formatar CEP
const formatCep = (cep: string) => {
    const cleanCep = cep.replace(/\D/g, '');
    return cleanCep.replace(/(\d{5})(\d{3})/, '$1-$2');
};

// Função para formatar número do cartão
const formatCardNumber = (cardNumber: string) => {
    const cleanCard = cardNumber.replace(/\D/g, '');
    return cleanCard.replace(/(\d{4})(\d{4})(\d{4})(\d{4})/, '$1 $2 $3 $4');
};

// Função para formatar data de expiração
const formatCardExpiry = (expiry: string) => {
    const cleanExpiry = expiry.replace(/\D/g, '');
    return cleanExpiry.replace(/(\d{2})(\d{2})/, '$1/$2');
};

// Função para formatar CVV
const formatCardCvv = (cvv: string) => {
    return cvv.replace(/\D/g, '').slice(0, 4);
};

// Watch para CEP
watch(() => customerData.value.cep, (newCep) => {
    if (newCep && newCep.length === 9) { // 00000-000
        searchAddressByCep(newCep);
    }
});

// Navegação entre steps
const nextStep = () => {
    if (isStepValid.value && currentStep.value < steps.value.length) {
        // Marcar step atual como completo
        const currentStepIndex = steps.value.findIndex((step: any) => step.id === currentStep.value);
        if (currentStepIndex !== -1) {
            steps.value[currentStepIndex].completed = true;
        }
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const goToStep = (stepId: number) => {
    // Só permite ir para steps já completados ou o próximo step
    const currentStepIndex = steps.value.findIndex((step: any) => step.id === currentStep.value);
    const targetStepIndex = steps.value.findIndex((step: any) => step.id === stepId);

    if (targetStepIndex <= currentStepIndex + 1) {
        currentStep.value = stepId;
    }
};

const processPayment = async () => {
    if (!isFormValid.value) {
        alert('Preencha todos os campos obrigatórios');
        return;
    }

    isProcessing.value = true;

    try {
        // Simulate payment processing
        await new Promise(resolve => setTimeout(resolve, 2000));
        alert('Pagamento processado com sucesso!');
    } catch {
        alert('Erro ao processar pagamento');
    } finally {
        isProcessing.value = false;
    }
};

// Função para animar order bump selection
const animateOrderBumpSelection = (orderBumpId: string, isChecked: boolean) => {
    // Garantir que orderBumpsChecked seja inicializado
    if (!orderBumpsChecked.value) {
        orderBumpsChecked.value = {};
    }

    orderBumpsChecked.value[orderBumpId] = isChecked;

    orderBumpAnimations.value[orderBumpId] = {
        isChecked,
        timestamp: Date.now()
    };
};

// Função para obter classes dinâmicas do order bump
const getOrderBumpClasses = (orderBumpId: string) => {
    const isChecked = orderBumpsChecked.value[orderBumpId];
    const animation = orderBumpAnimations.value[orderBumpId];

    return {
        card: {
            'order-bump-selected': isChecked,
            'order-bump-animating': animation && Date.now() - animation.timestamp < 500,
            'order-bump-pulse': animation && animation.isChecked && Date.now() - animation.timestamp < 300
        },
        checkbox: {
            'checkbox-selected': isChecked,
            'checkbox-pulse': animation && animation.isChecked && Date.now() - animation.timestamp < 300
        },
        price: {
            'price-selected': isChecked,
            'price-bounce': animation && animation.isChecked && Date.now() - animation.timestamp < 400
        }
    };
};

// Mapeamento de ícones para os métodos de pagamento
const paymentMethodIcons: Record<string, any> = {
    credit_card: CreditCardIcon,
    boleto: Receipt
};

// Processar paymentMethods para incluir os ícones corretos e filtrar por métodos habilitados
const processedPaymentMethods = computed(() => {
    const enabledMethods = props.checkout.enabled_payment_methods || ['pix', 'credit_card', 'boleto'];

    return paymentMethods
        .filter(method => enabledMethods.includes(method.method))
        .map(method => ({
            ...method,
            icon: method.isImageIcon ? null : paymentMethodIcons[method.method] || null,
            iconSrc: method.isImageIcon ? method.iconSrc : ''
        }));
});
</script>

<template>
    <div class="min-h-screen"
        :style="checkout.dark_mode ? 'background-color: #111827 !important; color: white;' : `background-color: ${checkout.background_color || '#f8fafc'} !important;`">
        <!-- Contador Regressivo -->
        <CountdownTimer :enabled="checkout.countdown_enabled" :icon="checkout.countdown_icon"
            :icon-type="checkout.countdown_icon_type" :duration="checkout.countdown_duration"
            :bg-color="checkout.countdown_bg_color" :text-color="checkout.countdown_text_color"
            :message="checkout.countdown_message" :start-time="checkout.countdown_start_time"
            :countdown-expired="checkout.countdown_expired" />

        <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-8"
            :class="{ 'pt-16 sm:pt-28': checkout.countdown_enabled }">
            <div class="max-w-2xl mx-auto" v-if="checkout.layout === 'single'">

                <!-- Banner Section -->
                <div v-if="checkout.banner"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 relative overflow-hidden mb-8 rounded-xl">
                    <img :src="`/storage/${checkout.banner}`" :alt="`Banner do checkout ${checkout.product.name}`"
                        class="w-full h-full object-cover" />
                </div>
                <!-- Layout Single Column -->
                <div class="rounded-xl sm:rounded-2xl shadow-xl border overflow-hidden"
                    :class="checkout.dark_mode ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-100'">

                    <!-- Steps Progress Bar -->
                    <div v-if="steps.length > 1" class="p-4 sm:p-6 border-b"
                        :class="checkout.dark_mode ? 'border-gray-700' : 'border-gray-200'">
                        <div class="flex items-center justify-between">
                            <div v-for="(step, index) in steps" :key="step.id"
                                class="flex items-center space-x-2 sm:space-x-3">
                                <!-- Step Circle -->
                                <div class="relative">
                                    <button
                                        @click="() => { goToStep(step.id); console.log('Step clicked:', step.id, 'Current step:', currentStep, 'Is active:', currentStep === step.id); }"
                                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center transition-all duration-200"
                                        :style="`background-color: ${currentStep === step.id ? checkout.step_active_color : checkout.step_inactive_color} !important; color: ${checkout.step_text_color} !important;`"
                                        :data-debug="`step-${step.id}-active-${currentStep === step.id}-color-${checkout.step_active_color}`"
                                        :title="`Step ${step.id}: ${currentStep === step.id ? 'Active' : step.completed ? 'Completed' : 'Inactive'} - Color: ${currentStep === step.id ? checkout.step_active_color : step.completed && currentStep !== step.id ? checkout.step_completed_color : checkout.step_inactive_color}`"
                                        :class="{
                                            'cursor-pointer': step.completed || index === 0 || index <= steps.findIndex(s => s.id === currentStep),
                                            'cursor-not-allowed': !step.completed && index > steps.findIndex(s => s.id === currentStep)
                                        }">
                                        <component :is="step.icon" class="w-4 h-4 sm:w-5 sm:h-5" />
                                    </button>

                                    <!-- Check mark for completed steps -->
                                    <div v-if="step.completed && currentStep !== step.id"
                                        class="absolute -top-1 -right-1 w-4 h-4 rounded-full flex items-center justify-center"
                                        :style="`background-color: ${checkout.step_completed_color || '#10b981'}`">
                                        <Check class="w-2 h-2"
                                            :style="`color: ${checkout.step_text_color || '#ffffff'}`" />
                                    </div>
                                </div>

                                <!-- Step Title -->
                                <div class="hidden sm:block">
                                    <div class="text-xs sm:text-sm font-medium" :style="{
                                        color: currentStep === step.id ? (checkout.step_active_color || '#3b82f6') :
                                            step.completed && currentStep !== step.id ? (checkout.step_completed_color || '#10b981') :
                                                '#6b7280'
                                    }">
                                        {{ step.title }}
                                    </div>
                                </div>

                                <!-- Connector Line -->
                                <div v-if="index < steps.length - 1" class="flex-1 h-0.5 mx-2 sm:mx-4"
                                    :style="`background-color: ${step.completed ? (checkout.step_completed_color || '#10b981') : '#d1d5db'}`">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 sm:p-8">
                        <!-- Step 1: Personal Data -->
                        <div v-if="currentStep === 1" class="mb-4 sm:mb-8">
                            <div class="flex items-center space-x-2 sm:space-x-3 mb-3 sm:mb-6">
                                <div class="w-7 h-7 sm:w-10 sm:h-10 rounded-full flex items-center justify-center"
                                    :class="checkout.dark_mode ? 'bg-blue-900' : 'bg-blue-100'">
                                    <User class="w-3 h-3 sm:w-5 sm:h-5"
                                        :class="checkout.dark_mode ? 'text-blue-400' : 'text-blue-600'" />
                                </div>
                                <div>
                                    <h3 class="text-sm sm:text-lg font-semibold"
                                        :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">Dados Pessoais</h3>
                                    <p class="text-xs sm:text-sm"
                                        :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-500'">Informe seus
                                        dados para o pagamento</p>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:gap-6">
                                <!-- Nome Completo -->
                                <div v-if="checkout.require_name" class="space-y-1 sm:space-y-2">
                                    <label for="name" class="block text-xs sm:text-sm font-medium"
                                        :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                        Nome Completo *
                                    </label>
                                    <input id="name" v-model="customerData.name" type="text"
                                        placeholder="Digite seu nome completo"
                                        class="w-full h-11 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base touch-manipulation"
                                        :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                        required />
                                </div>

                                <!-- Email -->
                                <div v-if="checkout.require_email" class="space-y-1 sm:space-y-2">
                                    <label for="email" class="block text-xs sm:text-sm font-medium"
                                        :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                        E-mail *
                                    </label>
                                    <input id="email" v-model="customerData.email" type="email"
                                        placeholder="Digite seu melhor e-mail"
                                        class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                        :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                        required />
                                </div>

                                <!-- Telefone -->
                                <div v-if="checkout.require_phone" class="space-y-1 sm:space-y-2">
                                    <label for="phone" class="block text-xs sm:text-sm font-medium"
                                        :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                        Telefone *
                                    </label>
                                    <input id="phone" v-model="customerData.phone" type="tel"
                                        placeholder="(11) 99999-9999"
                                        class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                        :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                        required />
                                </div>

                                <!-- CPF/CNPJ -->
                                <div v-if="checkout.require_cpf_cnpj" class="space-y-1 sm:space-y-2">
                                    <label for="cpf_cnpj" class="block text-xs sm:text-sm font-medium"
                                        :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                        CPF/CNPJ *
                                    </label>
                                    <input id="cpf_cnpj" v-model="customerData.cpf_cnpj" type="text"
                                        placeholder="000.000.000-00 ou 00.000.000/0000-00"
                                        class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                        :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                        required />
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Address Section -->
                        <div v-if="currentStep === 2 && (checkout.require_address || checkout.require_cep || checkout.require_city || checkout.require_state || checkout.require_neighborhood || checkout.require_number || checkout.require_complement)"
                            class="mb-6 sm:mb-8">
                            <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center"
                                    :class="checkout.dark_mode ? 'bg-purple-900' : 'bg-purple-100'">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5"
                                        :class="checkout.dark_mode ? 'text-purple-400' : 'text-purple-600'" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-semibold"
                                        :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">Endereço de Entrega
                                    </h3>
                                    <p class="text-xs sm:text-sm"
                                        :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-500'">Informe seu
                                        endereço para entrega</p>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:gap-6">


                                <!-- CEP -->
                                <div v-if="checkout.require_cep" class="space-y-1 sm:space-y-2">
                                    <label for="cep" class="block text-xs sm:text-sm font-medium"
                                        :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                        CEP *
                                    </label>
                                    <div class="relative">
                                        <input id="cep" v-model="customerData.cep" type="text" placeholder="00000-000"
                                            maxlength="9" @input="customerData.cep = formatCep($event.target.value)"
                                            class="w-full h-10 sm:h-12 px-3 sm:px-4 pr-10 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                            :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                            required />
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                            <Loader2 v-if="isSearchingCep" class="w-4 h-4 text-blue-500 animate-spin" />
                                            <svg v-else class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="text-xs text-blue-600 dark:text-blue-400">
                                        Digite o CEP para preenchimento automático do endereço
                                    </p>
                                </div>

                                <!-- Endereço Completo -->
                                <div v-if="checkout.require_address" class="space-y-1 sm:space-y-2">
                                    <label for="address" class="block text-xs sm:text-sm font-medium"
                                        :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                        Endereço Completo *
                                    </label>
                                    <input id="address" v-model="customerData.address" type="text"
                                        placeholder="Rua, Avenida, etc."
                                        class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                        :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                        required />
                                </div>

                                <!-- Cidade e Estado -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                    <!-- Cidade -->
                                    <div v-if="checkout.require_city" class="space-y-1 sm:space-y-2">
                                        <label for="city" class="block text-xs sm:text-sm font-medium"
                                            :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                            Cidade *
                                        </label>
                                        <input id="city" v-model="customerData.city" type="text"
                                            placeholder="Nome da cidade"
                                            class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                            :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                            required />
                                    </div>

                                    <!-- Estado -->
                                    <div v-if="checkout.require_state" class="space-y-1 sm:space-y-2">
                                        <label for="state" class="block text-xs sm:text-sm font-medium"
                                            :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                            Estado *
                                        </label>
                                        <input id="state" v-model="customerData.state" type="text" placeholder="UF"
                                            class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                            :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                            required />
                                    </div>
                                </div>

                                <!-- Bairro e Número -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                    <!-- Bairro -->
                                    <div v-if="checkout.require_neighborhood" class="space-y-1 sm:space-y-2">
                                        <label for="neighborhood" class="block text-xs sm:text-sm font-medium"
                                            :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                            Bairro *
                                        </label>
                                        <input id="neighborhood" v-model="customerData.neighborhood" type="text"
                                            placeholder="Nome do bairro"
                                            class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                            :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                            required />
                                    </div>

                                    <!-- Número -->
                                    <div v-if="checkout.require_number" class="space-y-1 sm:space-y-2">
                                        <label for="number" class="block text-xs sm:text-sm font-medium"
                                            :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                            Número *
                                        </label>
                                        <input id="number" v-model="customerData.number" type="text"
                                            placeholder="Número"
                                            class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                            :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                            required />
                                    </div>
                                </div>

                                <!-- Complemento -->
                                <div v-if="checkout.require_complement" class="space-y-1 sm:space-y-2">
                                    <label for="complement" class="block text-xs sm:text-sm font-medium"
                                        :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                        Complemento *
                                    </label>
                                    <input id="complement" v-model="customerData.complement" type="text"
                                        placeholder="Apartamento, sala, etc."
                                        class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                        :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                        required />
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Payment Method Section -->
                        <div v-if="currentStep === 3" class="mb-6 sm:mb-8">
                            <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center"
                                    :class="checkout.dark_mode ? 'bg-green-900' : 'bg-green-100'">
                                    <CreditCard class="w-4 h-4 sm:w-5 sm:h-5"
                                        :class="checkout.dark_mode ? 'text-green-400' : 'text-green-600'" />
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-semibold"
                                        :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">Método de
                                        Pagamento</h3>
                                    <p class="text-xs sm:text-sm"
                                        :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-500'">Escolha como
                                        deseja pagar</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <PaymentMethodOption v-for="method in processedPaymentMethods" :key="method.method"
                                    :method="method.method" :title="method.title" :description="method.description"
                                    :icon="method.icon" :icon-src="method.iconSrc" :is-image-icon="method.isImageIcon"
                                    v-model="paymentMethod" :colors="customPaymentMethodColors[method.method]" />
                            </div>
                        </div>

                        <!-- Step 2: Payment Method Section (when no address fields) -->
                        <div v-if="currentStep === 2 && !(checkout.require_address || checkout.require_cep || checkout.require_city || checkout.require_state || checkout.require_neighborhood || checkout.require_number || checkout.require_complement)"
                            class="mb-6 sm:mb-8">
                            <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center"
                                    :class="checkout.dark_mode ? 'bg-green-900' : 'bg-green-100'">
                                    <CreditCard class="w-4 h-4 sm:w-5 sm:h-5"
                                        :class="checkout.dark_mode ? 'text-green-400' : 'text-green-600'" />
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-semibold"
                                        :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">Método de
                                        Pagamento</h3>
                                    <p class="text-xs sm:text-sm"
                                        :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-500'">Escolha como
                                        deseja pagar</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <PaymentMethodOption v-for="method in processedPaymentMethods" :key="method.method"
                                    :method="method.method" :title="method.title" :description="method.description"
                                    :icon="method.icon" :icon-src="method.iconSrc" :is-image-icon="method.isImageIcon"
                                    v-model="paymentMethod" :colors="customPaymentMethodColors[method.method]" />
                            </div>
                        </div>

                        <!-- Step 4: Order Bumps Section -->
                        <div v-if="currentStep === 4 && orderBumps.length > 0" class="mb-6 sm:mb-8">
                            <!-- Header -->
                            <div class="flex items-center space-x-2 mb-4">
                                <div
                                    class="w-6 h-6 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 flex items-center justify-center star-icon">
                                    <span class="text-white text-xs font-bold">★</span>
                                </div>
                                <h4 class="text-sm font-semibold"
                                    :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">
                                    Você também pode gostar de:
                                </h4>
                            </div>

                            <!-- Order Bumps Cards -->
                            <div class="space-y-4">
                                <OrderBump v-for="orderBump in orderBumps" :key="orderBump.id" :order-bump="orderBump"
                                    :order-bumps-checked="orderBumpsChecked"
                                    :order-bump-animations="orderBumpAnimations" :checkout="checkout"
                                    :format-price="formatPrice" :get-order-bump-classes="getOrderBumpClasses"
                                    :animate-order-bump-selection="animateOrderBumpSelection" />
                            </div>
                        </div>

                        <!-- Order Summary (always visible) -->
                        <div class="rounded-lg sm:rounded-xl p-4 sm:p-6 mb-6 sm:mb-8 relative"
                            :class="checkout.dark_mode ? 'bg-gray-700' : 'bg-gray-50'">
                            <!-- Order Bumps Available Indicator -->
                            <div v-if="orderBumps.length > 0"
                                class="absolute -top-2 -right-2 w-6 h-6 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center animate-pulse">
                                <span class="text-white text-xs font-bold">{{ orderBumps.length }}</span>
                            </div>
                            <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                                <ShoppingBag class="w-4 h-4 sm:w-5 sm:h-5"
                                    :class="checkout.dark_mode ? 'text-gray-400' : 'text-gray-600'" />
                                <h4 class="font-semibold text-sm sm:text-base"
                                    :class="checkout.dark_mode ? 'text-white' : 'text-gray-700'">Sua compra</h4>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center space-x-3">
                                    <img :src="`/${checkout.product.image}`" :alt="checkout.product.name"
                                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold mb-1 text-sm sm:text-base"
                                            :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">{{
                                                checkout.product.name }}</h4>
                                        <p class="text-xs sm:text-sm mb-2"
                                            :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-600'">{{
                                                checkout.product.description }}</p>

                                    </div>
                                </div>
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span
                                        :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-600'">Subtotal</span>
                                    <span :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">{{
                                        formatPrice(checkout.product.price) }}</span>
                                </div>
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span
                                        :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-600'">Desconto</span>
                                    <span class="text-green-600">R$ 0,00</span>
                                </div>
                                <div class="border-t pt-3"
                                    :class="checkout.dark_mode ? 'border-gray-600' : 'border-gray-200'">
                                    <div class="flex justify-between items-center">
                                        <span class="text-base sm:text-lg font-semibold"
                                            :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">Total</span>
                                        <span class="text-xl sm:text-2xl font-bold"
                                            :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">{{
                                                formatPrice(totalPrice) }}</span>
                                    </div>
                                </div>
                                <!-- Order Bumps Section for Two Column Layout -->
                                <div v-if="orderBumps.length > 0 && checkout.layout === 'two-column'"
                                    class="mb-6 sm:mb-8">
                                    <!-- Header -->
                                    <div class="flex items-center space-x-2 mb-4">
                                        <div
                                            class="w-6 h-6 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 flex items-center justify-center star-icon">
                                            <span class="text-white text-xs font-bold">★</span>
                                        </div>
                                        <h4 class="text-sm font-semibold"
                                            :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">
                                            Você também pode gostar de:
                                        </h4>
                                    </div>

                                    <!-- Order Bumps Cards -->
                                    <div class="space-y-4">
                                        <div v-for="orderBump in orderBumps" :key="orderBump.id"
                                            class="order-bump-card relative overflow-hidden rounded-2xl transition-all duration-300 transform hover:scale-[1.02]"
                                            :class="checkout.dark_mode ? 'dark' : ''">
                                            <div class="p-4 sm:p-6">
                                                <div class="flex items-start space-x-4">
                                                    <!-- Product Image -->
                                                    <div class="flex-shrink-0">
                                                        <div
                                                            class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                                                            <img v-if="orderBump.image" :src="`/${orderBump.image}`"
                                                                :alt="orderBump.name"
                                                                class="w-full h-full object-cover" />
                                                            <div v-else
                                                                class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                                                                {{ orderBump.name.charAt(0) }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Product Details -->
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center space-x-2 mb-2">
                                                            <h5 class="text-sm sm:text-base font-bold"
                                                                :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">
                                                                {{ orderBump.name }}
                                                            </h5>
                                                            <span class="text-orange-500 gift-emoji">🎁</span>
                                                        </div>
                                                        <p class="text-xs sm:text-sm mb-3"
                                                            :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-600'">
                                                            {{ orderBump.description }}
                                                        </p>
                                                        <div class="flex items-center justify-between">
                                                            <span class="text-lg sm:text-xl font-bold price-gradient">
                                                                + {{ formatPrice(orderBump.price) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Call to Action Bar -->
                                            <div
                                                class="bg-gradient-to-r from-green-500 to-green-600 border-t-2 border-dashed border-yellow-400 p-4">
                                                <label :for="`order-bump-two-column-${orderBump.id}`"
                                                    class="flex items-center space-x-3 cursor-pointer">
                                                    <div class="relative">
                                                        <input type="checkbox"
                                                            :id="`order-bump-two-column-${orderBump.id}`"
                                                            v-model="orderBumpsChecked[orderBump.id]"
                                                            class="sr-only peer" />
                                                        <div
                                                            class="w-6 h-6 rounded-full border-2 border-white flex items-center justify-center peer-checked:bg-white peer-checked:border-green-600 transition-all duration-200">
                                                            <div
                                                                class="w-4 h-4 rounded-full bg-green-600 opacity-0 peer-checked:opacity-100 transition-all duration-200 scale-0 peer-checked:scale-100">
                                                                <Check class="w-3 h-3 text-white" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="text-white font-bold text-sm sm:text-base">
                                                        Quero comprar também!
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Credit Card Fields for Two Column Layout -->
                                <div v-if="paymentMethod === 'credit_card'" class="mt-6 sm:mt-8">
                                    <div
                                        class="credit-card-fields grid gap-4 sm:gap-6 animate-in slide-in-from-top-2 duration-300">
                                        <!-- Número do Cartão -->
                                        <div
                                            class="space-y-1 sm:space-y-2 animate-in slide-in-from-left-2 duration-300 delay-100">
                                            <label for="card_number_two_column"
                                                class="block text-xs sm:text-sm font-medium"
                                                :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                                Número do Cartão *
                                            </label>
                                            <input id="card_number_two_column" v-model="customerData.card_number"
                                                type="text" placeholder="0000 0000 0000 0000" maxlength="19"
                                                @input="customerData.card_number = formatCardNumber($event.target.value)"
                                                class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                                required />
                                        </div>

                                        <!-- Nome do Titular -->
                                        <div
                                            class="space-y-1 sm:space-y-2 animate-in slide-in-from-left-2 duration-300 delay-200">
                                            <label for="card_holder_two_column"
                                                class="block text-xs sm:text-sm font-medium"
                                                :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                                Nome do Titular *
                                            </label>
                                            <input id="card_holder_two_column" v-model="customerData.card_holder"
                                                type="text" placeholder="Como está no cartão"
                                                class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                                required />
                                        </div>

                                        <!-- Data de Expiração e CVV -->
                                        <div
                                            class="grid grid-cols-2 gap-4 sm:gap-6 animate-in slide-in-from-left-2 duration-300 delay-300">
                                            <div class="space-y-1 sm:space-y-2">
                                                <label for="card_expiry_two_column"
                                                    class="block text-xs sm:text-sm font-medium"
                                                    :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                                    Validade *
                                                </label>
                                                <input id="card_expiry_two_column" v-model="customerData.card_expiry"
                                                    type="text" placeholder="MM/AA" maxlength="5"
                                                    @input="customerData.card_expiry = formatCardExpiry($event.target.value)"
                                                    class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                    :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                                    required />
                                            </div>

                                            <div class="space-y-1 sm:space-y-2">
                                                <label for="card_cvv_two_column"
                                                    class="block text-xs sm:text-sm font-medium"
                                                    :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                                    CVV *
                                                </label>
                                                <input id="card_cvv_two_column" v-model="customerData.card_cvv"
                                                    type="text" placeholder="123" maxlength="4"
                                                    @input="customerData.card_cvv = formatCardCvv($event.target.value)"
                                                    class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                    :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                                    required />
                                            </div>
                                        </div>

                                        <!-- Parcelas -->
                                        <div
                                            class="space-y-1 sm:space-y-2 animate-in slide-in-from-left-2 duration-300 delay-400">
                                            <label for="card_installments_two_column"
                                                class="block text-xs sm:text-sm font-medium"
                                                :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                                Parcelas
                                            </label>
                                            <select id="card_installments_two_column"
                                                v-model="customerData.card_installments"
                                                class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'">
                                                <option value="1">1x de {{ formatPrice(totalPrice) }} sem juros</option>
                                                <option value="2">2x de {{ formatPrice(totalPrice / 2) }} sem juros
                                                </option>
                                                <option value="3">3x de {{ formatPrice(totalPrice / 3) }} sem juros
                                                </option>
                                                <option value="6">6x de {{ formatPrice(totalPrice / 6) }} sem juros
                                                </option>
                                                <option value="12">12x de {{ formatPrice(totalPrice / 12) }} sem juros
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Navigation Buttons for Two Column Layout -->
                                <div class="mt-4 sm:mt-6">
                                    <div class="flex space-x-3 sm:space-x-4">
                                        <!-- Back Button -->
                                        <button v-if="currentStep > 1" @click="prevStep"
                                            class="flex-1 h-12 sm:h-14 px-4 sm:px-6 border-2 rounded-lg sm:rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 sm:space-x-3 font-bold text-sm sm:text-base"
                                            :class="checkout.dark_mode ? 'border-gray-600 text-gray-300 hover:border-gray-500 hover:bg-gray-700' : 'border-gray-300 text-gray-700 hover:border-gray-400 hover:bg-gray-50'">
                                            <ChevronLeft class="w-4 h-4 sm:w-5 sm:h-5" />
                                            <span>Voltar</span>
                                        </button>

                                        <!-- Next/Finish Button -->
                                        <button v-if="currentStep < steps.length" @click="nextStep"
                                            :disabled="!isStepValid"
                                            class="flex-1 h-12 sm:h-14 px-4 sm:px-6 rounded-lg sm:rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 sm:space-x-3 font-bold text-sm sm:text-base shadow-lg hover:shadow-xl disabled:shadow-none disabled:cursor-not-allowed transform hover:scale-[1.02] disabled:transform-none"
                                            :style="isStepValid ? getButtonClasses.customStyle : {}"
                                            :class="!isStepValid ? (checkout.dark_mode ? 'bg-gray-600 text-gray-400 cursor-not-allowed' : 'bg-gray-300 text-gray-500 cursor-not-allowed') : 'text-white hover:opacity-90'">
                                            <span>{{ currentStep === steps.length - 1 ? 'Finalizar' : 'Próximo'
                                                }}</span>
                                            <ChevronRight class="w-4 h-4 sm:w-5 sm:h-5" />
                                        </button>

                                        <!-- Final Payment Button -->
                                        <button v-if="currentStep === steps.length" @click="processPayment"
                                            :disabled="!isFormValid || isProcessing"
                                            :style="getButtonClasses.customStyle"
                                            class="flex-1 h-12 sm:h-14 text-white font-bold rounded-lg sm:rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 sm:space-x-3 shadow-lg hover:shadow-xl disabled:shadow-none disabled:cursor-not-allowed transform hover:scale-[1.02] disabled:transform-none text-sm sm:text-base hover:opacity-90">
                                            <Loader2 v-if="isProcessing" class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" />
                                            <ArrowRight v-else class="w-4 h-4 sm:w-5 sm:h-5" />
                                            <span>{{ isProcessing ? 'Processando Pagamento...' : 'Finalizar Compra'
                                                }}</span>
                                        </button>
                                    </div>

                                    <!-- Security Info -->
                                    <div class="mt-4 sm:mt-6 text-center">
                                        <div class="flex items-center justify-center space-x-2"
                                            :class="checkout.dark_mode ? 'text-gray-400' : 'text-gray-500'">
                                            <ShieldCheck class="w-3 h-3 sm:w-4 sm:h-4" />
                                            <span class="text-xs sm:text-sm">Seus dados estão protegidos com
                                                criptografia
                                                SSL</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Layout Two Column -->
            <div class="max-w-6xl mx-auto" v-if="checkout.layout === 'two-column'">
                <div class="grid gap-6 sm:gap-8 lg:grid-cols-2">
                    <!-- Coluna da Esquerda - Formulário -->
                    <div class="rounded-xl sm:rounded-2xl shadow-xl border overflow-hidden"
                        :class="checkout.dark_mode ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-100'">
                        <!-- Banner Section -->
                        <div v-if="checkout.banner"
                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 relative overflow-hidden">
                            <img :src="`/storage/${checkout.banner}`"
                                :alt="`Banner do checkout ${checkout.product.name}`"
                                class="w-full h-full object-cover" />
                        </div>

                        <!-- Steps Progress Bar for Two Column Layout -->
                        <div v-if="steps.length > 1" class="p-4 sm:p-6 border-b"
                            :class="checkout.dark_mode ? 'border-gray-700' : 'border-gray-200'">
                            <div class="flex items-center justify-between">
                                <div v-for="(step, index) in steps" :key="step.id"
                                    class="flex items-center space-x-2 sm:space-x-3">
                                    <!-- Step Circle -->
                                    <div class="relative">
                                        <button @click="goToStep(step.id)"
                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center transition-all duration-200"
                                            :class="{
                                                'cursor-pointer': step.completed || index === 0 || index <= steps.findIndex(s => s.id === currentStep),
                                                'cursor-not-allowed': !step.completed && index > steps.findIndex(s => s.id === currentStep)
                                            }"
                                            :style="`background-color: ${currentStep === step.id ? checkout.step_active_color : step.completed && currentStep !== step.id ? checkout.step_completed_color : checkout.step_inactive_color} !important; color: ${checkout.step_text_color} !important;`">
                                            <component :is="step.icon" class="w-4 h-4 sm:w-5 sm:h-5" />
                                        </button>

                                        <!-- Check mark for completed steps -->
                                        <div v-if="step.completed && currentStep !== step.id"
                                            class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full flex items-center justify-center">
                                            <Check class="w-2 h-2 text-white" />
                                        </div>
                                    </div>

                                    <!-- Step Title -->
                                    <div class="hidden sm:block">
                                        <div class="text-xs sm:text-sm font-medium"
                                            :style="`color: ${currentStep === step.id ? checkout.step_active_color : step.completed && currentStep !== step.id ? checkout.step_completed_color : checkout.step_inactive_color} !important;`">
                                            {{ step.title }}
                                        </div>
                                    </div>

                                    <!-- Connector Line -->
                                    <div v-if="index < steps.length - 1" class="flex-1 h-0.5 mx-2 sm:mx-4"
                                        :style="`background-color: ${step.completed ? checkout.step_completed_color : checkout.step_inactive_color} !important;`">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 sm:p-8">
                            <!-- Step 1: Personal Data Section -->
                            <div v-if="currentStep === 1" class="mb-6 sm:mb-8">
                                <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center"
                                        :class="checkout.dark_mode ? 'bg-blue-900' : 'bg-blue-100'">
                                        <User class="w-4 h-4 sm:w-5 sm:h-5"
                                            :class="checkout.dark_mode ? 'text-blue-400' : 'text-blue-600'" />
                                    </div>
                                    <div>
                                        <h3 class="text-base sm:text-lg font-semibold"
                                            :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">Dados Pessoais
                                        </h3>
                                        <p class="text-xs sm:text-sm"
                                            :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-500'">Informe seus
                                            dados para o pagamento</p>
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:gap-6">
                                    <div class="space-y-1 sm:space-y-2">
                                        <label for="name" class="block text-xs sm:text-sm font-medium"
                                            :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                            Nome Completo *
                                        </label>
                                        <input id="name" v-model="customerData.name" type="text"
                                            placeholder="Digite seu nome completo"
                                            class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                            :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                            required />
                                    </div>

                                    <div class="space-y-1 sm:space-y-2">
                                        <label for="email" class="block text-xs sm:text-sm font-medium"
                                            :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                            E-mail *
                                        </label>
                                        <input id="email" v-model="customerData.email" type="email"
                                            placeholder="Digite seu melhor e-mail"
                                            class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                            :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                            required />
                                    </div>

                                    <div class="space-y-1 sm:space-y-2">
                                        <label for="phone" class="block text-xs sm:text-sm font-medium"
                                            :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                            Telefone
                                        </label>
                                        <input id="phone" v-model="customerData.phone" type="tel"
                                            placeholder="(11) 99999-9999"
                                            class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                            :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'" />
                                    </div>

                                    <div class="space-y-1 sm:space-y-2">
                                        <label for="cpf_cnpj" class="block text-xs sm:text-sm font-medium"
                                            :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                            CPF/CNPJ
                                        </label>
                                        <input id="cpf_cnpj" v-model="customerData.cpf_cnpj" type="text"
                                            placeholder="000.000.000-00 ou 00.000.000/0000-00"
                                            class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                            :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'" />
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Address Section (if needed) -->
                            <div v-if="currentStep === 2 && (checkout.require_address || checkout.require_cep || checkout.require_city || checkout.require_state || checkout.require_neighborhood || checkout.require_number || checkout.require_complement)"
                                class="mb-6 sm:mb-8">
                                <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center"
                                        :class="checkout.dark_mode ? 'bg-purple-900' : 'bg-purple-100'">
                                        <MapPin class="w-4 h-4 sm:w-5 sm:h-5"
                                            :class="checkout.dark_mode ? 'text-purple-400' : 'text-purple-600'" />
                                    </div>
                                    <div>
                                        <h3 class="text-base sm:text-lg font-semibold"
                                            :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">Endereço de
                                            Entrega
                                        </h3>
                                        <p class="text-xs sm:text-sm"
                                            :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-500'">Informe seu
                                            endereço para entrega</p>
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:gap-6">
                                    <!-- CEP -->
                                    <div v-if="checkout.require_cep" class="space-y-1 sm:space-y-2">
                                        <label for="cep" class="block text-xs sm:text-sm font-medium"
                                            :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                            CEP *
                                        </label>
                                        <div class="relative">
                                            <input id="cep" v-model="customerData.cep" type="text"
                                                placeholder="00000-000" maxlength="9"
                                                @input="customerData.cep = formatCep($event.target.value)"
                                                class="w-full h-10 sm:h-12 px-3 sm:px-4 pr-10 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                                required />
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <Loader2 v-if="isSearchingCep"
                                                    class="w-4 h-4 text-blue-500 animate-spin" />
                                                <svg v-else class="w-4 h-4 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <p class="text-xs text-blue-600 dark:text-blue-400">
                                            Digite o CEP para preenchimento automático do endereço
                                        </p>
                                    </div>

                                    <!-- Endereço Completo -->
                                    <div v-if="checkout.require_address" class="space-y-1 sm:space-y-2">
                                        <label for="address" class="block text-xs sm:text-sm font-medium"
                                            :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                            Endereço Completo *
                                        </label>
                                        <input id="address" v-model="customerData.address" type="text"
                                            placeholder="Rua, Avenida, etc."
                                            class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                            :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                            required />
                                    </div>

                                    <!-- Cidade e Estado -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                        <!-- Cidade -->
                                        <div v-if="checkout.require_city" class="space-y-1 sm:space-y-2">
                                            <label for="city" class="block text-xs sm:text-sm font-medium"
                                                :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                                Cidade *
                                            </label>
                                            <input id="city" v-model="customerData.city" type="text"
                                                placeholder="Nome da cidade"
                                                class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                                required />
                                        </div>

                                        <!-- Estado -->
                                        <div v-if="checkout.require_state" class="space-y-1 sm:space-y-2">
                                            <label for="state" class="block text-xs sm:text-sm font-medium"
                                                :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                                Estado *
                                            </label>
                                            <input id="state" v-model="customerData.state" type="text" placeholder="UF"
                                                class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                                required />
                                        </div>
                                    </div>

                                    <!-- Bairro e Número -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                        <!-- Bairro -->
                                        <div v-if="checkout.require_neighborhood" class="space-y-1 sm:space-y-2">
                                            <label for="neighborhood" class="block text-xs sm:text-sm font-medium"
                                                :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                                Bairro *
                                            </label>
                                            <input id="neighborhood" v-model="customerData.neighborhood" type="text"
                                                placeholder="Nome do bairro"
                                                class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                                required />
                                        </div>

                                        <!-- Número -->
                                        <div v-if="checkout.require_number" class="space-y-1 sm:space-y-2">
                                            <label for="number" class="block text-xs sm:text-sm font-medium"
                                                :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                                Número *
                                            </label>
                                            <input id="number" v-model="customerData.number" type="text"
                                                placeholder="Número"
                                                class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                                required />
                                        </div>
                                    </div>

                                    <!-- Complemento -->
                                    <div v-if="checkout.require_complement" class="space-y-1 sm:space-y-2">
                                        <label for="complement" class="block text-xs sm:text-sm font-medium"
                                            :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                            Complemento *
                                        </label>
                                        <input id="complement" v-model="customerData.complement" type="text"
                                            placeholder="Apartamento, sala, etc."
                                            class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                            :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                            required />
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Payment Method Section -->
                            <div v-if="currentStep === 3" class="mb-6 sm:mb-8">
                                <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center"
                                        :class="checkout.dark_mode ? 'bg-green-900' : 'bg-green-100'">
                                        <CreditCard class="w-4 h-4 sm:w-5 sm:h-5"
                                            :class="checkout.dark_mode ? 'text-green-400' : 'text-green-600'" />
                                    </div>
                                    <div>
                                        <h3 class="text-base sm:text-lg font-semibold"
                                            :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">Método de
                                            Pagamento</h3>
                                        <p class="text-xs sm:text-sm"
                                            :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-500'">Escolha como
                                            deseja pagar</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <PaymentMethodOption v-for="method in processedPaymentMethods" :key="method.method"
                                        :method="method.method" :title="method.title" :description="method.description"
                                        :icon="method.icon" :icon-src="method.iconSrc"
                                        :is-image-icon="method.isImageIcon" v-model="paymentMethod"
                                        :colors="customPaymentMethodColors[method.method]" />
                                </div>
                            </div>

                            <!-- Step 2: Payment Method Section (when no address fields) -->
                            <div v-if="currentStep === 2 && !(checkout.require_address || checkout.require_cep || checkout.require_city || checkout.require_state || checkout.require_neighborhood || checkout.require_number || checkout.require_complement)"
                                class="mb-6 sm:mb-8">
                                <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center"
                                        :class="checkout.dark_mode ? 'bg-green-900' : 'bg-green-100'">
                                        <CreditCard class="w-4 h-4 sm:w-5 sm:h-5"
                                            :class="checkout.dark_mode ? 'text-green-400' : 'text-green-600'" />
                                    </div>
                                    <div>
                                        <h3 class="text-base sm:text-lg font-semibold"
                                            :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">Método de
                                            Pagamento</h3>
                                        <p class="text-xs sm:text-sm"
                                            :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-500'">Escolha como
                                            deseja pagar</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <PaymentMethodOption v-for="method in processedPaymentMethods" :key="method.method"
                                        :method="method.method" :title="method.title" :description="method.description"
                                        :icon="method.icon" :icon-src="method.iconSrc"
                                        :is-image-icon="method.isImageIcon" v-model="paymentMethod"
                                        :colors="customPaymentMethodColors[method.method]" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna da Direita - Resumo do Pedido -->
                    <div class="rounded-xl sm:rounded-2xl shadow-xl border p-4 sm:p-8"
                        :class="checkout.dark_mode ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-100'">
                        <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                            <ShoppingBag class="w-4 h-4 sm:w-5 sm:h-5"
                                :class="checkout.dark_mode ? 'text-gray-400' : 'text-gray-600'" />
                            <h4 class="font-semibold text-sm sm:text-base"
                                :class="checkout.dark_mode ? 'text-white' : 'text-gray-700'">Resumo do Pedido</h4>
                        </div>

                        <div class="space-y-4 sm:space-y-6 relative">
                            <!-- Order Bumps Available Indicator for Two Column -->
                            <div v-if="orderBumps.length > 0"
                                class="absolute -top-2 -right-2 w-6 h-6 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center animate-pulse z-10">
                                <span class="text-white text-xs font-bold">{{ orderBumps.length }}</span>
                            </div>
                            <!-- Produto -->
                            <div class="flex items-center space-x-3 sm:space-x-4 p-3 sm:p-4 rounded-lg sm:rounded-xl"
                                :class="checkout.dark_mode ? 'bg-gray-700' : 'bg-gray-50'">
                                <img :src="`/${checkout.product.image}`" :alt="checkout.product.name"
                                    class="w-12 h-12 sm:w-16 sm:h-16 rounded-lg object-cover">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold mb-1 text-sm sm:text-base"
                                        :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">{{
                                            checkout.product.name }}</h4>
                                    <p class="text-xs sm:text-sm mb-2"
                                        :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-600'">{{
                                            checkout.product.description }}</p>

                                </div>
                            </div>

                            <!-- Resumo Financeiro -->
                            <div class="space-y-3">
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span
                                        :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-600'">Subtotal</span>
                                    <span :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">{{
                                        formatPrice(checkout.product.price) }}</span>
                                </div>
                                <!-- Order Bumps Summary -->
                                <div v-for="orderBump in selectedOrderBumps" :key="orderBump.id"
                                    class="flex justify-between text-xs sm:text-sm">
                                    <span :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-600'">
                                        + {{ orderBump.name }}
                                    </span>
                                    <span :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">
                                        {{ formatPrice(orderBump.price) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span
                                        :class="checkout.dark_mode ? 'text-gray-300' : 'text-gray-600'">Desconto</span>
                                    <span class="text-green-600">R$ 0,00</span>
                                </div>
                                <div class="border-t pt-3"
                                    :class="checkout.dark_mode ? 'border-gray-600' : 'border-gray-200'">
                                    <div class="flex justify-between items-center">
                                        <span class="text-base sm:text-lg font-semibold"
                                            :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">Total</span>
                                        <span class="text-xl sm:text-2xl font-bold total-price"
                                            :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'"
                                            :style="{ animation: selectedOrderBumps.length > 0 ? 'totalUpdate 0.5s ease-out' : 'none' }">
                                            {{ formatPrice(totalPrice) }}
                                        </span>
                                    </div>

                                </div>
                                <!-- Order Bumps Section for Two Column Layout -->
                                <div v-if="orderBumps.length > 0 && checkout.layout === 'two-column'"
                                    class="mb-6 sm:mb-8">
                                    <!-- Header -->
                                    <div class="flex items-center space-x-2 mb-4">
                                        <div
                                            class="w-6 h-6 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 flex items-center justify-center star-icon">
                                            <span class="text-white text-xs font-bold">★</span>
                                        </div>
                                        <h4 class="text-sm font-semibold"
                                            :class="checkout.dark_mode ? 'text-white' : 'text-gray-900'">
                                            Você também pode gostar de:
                                        </h4>
                                    </div>

                                    <!-- Order Bumps Cards -->
                                    <div class="space-y-4">
                                        <OrderBump v-for="orderBump in orderBumps" :key="orderBump.id"
                                            :order-bump="orderBump" :order-bumps-checked="orderBumpsChecked"
                                            :order-bump-animations="orderBumpAnimations" :checkout="checkout"
                                            :format-price="formatPrice" :get-order-bump-classes="getOrderBumpClasses"
                                            :animate-order-bump-selection="animateOrderBumpSelection" />
                                    </div>
                                </div>

                                <!-- Credit Card Fields for Two Column Layout -->
                                <div v-if="paymentMethod === 'credit_card'" class="mt-6 sm:mt-8">
                                    <div
                                        class="credit-card-fields grid gap-4 sm:gap-6 animate-in slide-in-from-top-2 duration-300">
                                        <!-- Número do Cartão -->
                                        <div
                                            class="space-y-1 sm:space-y-2 animate-in slide-in-from-left-2 duration-300 delay-100">
                                            <label for="card_number_two_column"
                                                class="block text-xs sm:text-sm font-medium"
                                                :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                                Número do Cartão *
                                            </label>
                                            <input id="card_number_two_column" v-model="customerData.card_number"
                                                type="text" placeholder="0000 0000 0000 0000" maxlength="19"
                                                @input="customerData.card_number = formatCardNumber($event.target.value)"
                                                class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                                required />
                                        </div>

                                        <!-- Nome do Titular -->
                                        <div
                                            class="space-y-1 sm:space-y-2 animate-in slide-in-from-left-2 duration-300 delay-200">
                                            <label for="card_holder_two_column"
                                                class="block text-xs sm:text-sm font-medium"
                                                :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                                Nome do Titular *
                                            </label>
                                            <input id="card_holder_two_column" v-model="customerData.card_holder"
                                                type="text" placeholder="Como está no cartão"
                                                class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                                required />
                                        </div>

                                        <!-- Data de Expiração e CVV -->
                                        <div
                                            class="grid grid-cols-2 gap-4 sm:gap-6 animate-in slide-in-from-left-2 duration-300 delay-300">
                                            <div class="space-y-1 sm:space-y-2">
                                                <label for="card_expiry_two_column"
                                                    class="block text-xs sm:text-sm font-medium"
                                                    :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                                    Validade *
                                                </label>
                                                <input id="card_expiry_two_column" v-model="customerData.card_expiry"
                                                    type="text" placeholder="MM/AA" maxlength="5"
                                                    @input="customerData.card_expiry = formatCardExpiry($event.target.value)"
                                                    class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                    :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                                    required />
                                            </div>

                                            <div class="space-y-1 sm:space-y-2">
                                                <label for="card_cvv_two_column"
                                                    class="block text-xs sm:text-sm font-medium"
                                                    :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                                    CVV *
                                                </label>
                                                <input id="card_cvv_two_column" v-model="customerData.card_cvv"
                                                    type="text" placeholder="123" maxlength="4"
                                                    @input="customerData.card_cvv = formatCardCvv($event.target.value)"
                                                    class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                    :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'"
                                                    required />
                                            </div>
                                        </div>

                                        <!-- Parcelas -->
                                        <div
                                            class="space-y-1 sm:space-y-2 animate-in slide-in-from-left-2 duration-300 delay-400">
                                            <label for="card_installments_two_column"
                                                class="block text-xs sm:text-sm font-medium"
                                                :class="checkout.dark_mode ? 'text-gray-200' : 'text-gray-700'">
                                                Parcelas
                                            </label>
                                            <select id="card_installments_two_column"
                                                v-model="customerData.card_installments"
                                                class="w-full h-10 sm:h-12 px-3 sm:px-4 border rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-sm sm:text-base"
                                                :class="checkout.dark_mode ? 'bg-gray-700 border-gray-600 text-white focus:border-blue-500 focus:ring-blue-900' : 'border-gray-200 focus:border-blue-500'">
                                                <option value="1">1x de {{ formatPrice(totalPrice) }} sem juros</option>
                                                <option value="2">2x de {{ formatPrice(totalPrice / 2) }} sem juros
                                                </option>
                                                <option value="3">3x de {{ formatPrice(totalPrice / 3) }} sem juros
                                                </option>
                                                <option value="6">6x de {{ formatPrice(totalPrice / 6) }} sem juros
                                                </option>
                                                <option value="12">12x de {{ formatPrice(totalPrice / 12) }} sem juros
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Navigation Buttons for Two Column Layout -->
                                <div class="mt-4 sm:mt-6">
                                    <div class="flex space-x-3 sm:space-x-4">
                                        <!-- Back Button -->
                                        <button v-if="currentStep > 1" @click="prevStep"
                                            class="flex-1 h-12 sm:h-14 px-4 sm:px-6 border-2 rounded-lg sm:rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 sm:space-x-3 font-bold text-sm sm:text-base"
                                            :class="checkout.dark_mode ? 'border-gray-600 text-gray-300 hover:border-gray-500 hover:bg-gray-700' : 'border-gray-300 text-gray-700 hover:border-gray-400 hover:bg-gray-50'">
                                            <ChevronLeft class="w-4 h-4 sm:w-5 sm:h-5" />
                                            <span>Voltar</span>
                                        </button>

                                        <!-- Next/Finish Button -->
                                        <button v-if="currentStep < steps.length" @click="nextStep"
                                            :disabled="!isStepValid"
                                            class="flex-1 h-12 sm:h-14 px-4 sm:px-6 rounded-lg sm:rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 sm:space-x-3 font-bold text-sm sm:text-base shadow-lg hover:shadow-xl disabled:shadow-none disabled:cursor-not-allowed transform hover:scale-[1.02] disabled:transform-none"
                                            :style="isStepValid ? getButtonClasses.customStyle : {}"
                                            :class="!isStepValid ? (checkout.dark_mode ? 'bg-gray-600 text-gray-400 cursor-not-allowed' : 'bg-gray-300 text-gray-500 cursor-not-allowed') : 'text-white hover:opacity-90'">
                                            <span>{{ currentStep === steps.length - 1 ? 'Finalizar' : 'Próximo'
                                            }}</span>
                                            <ChevronRight class="w-4 h-4 sm:w-5 sm:h-5" />
                                        </button>

                                        <!-- Final Payment Button -->
                                        <button v-if="currentStep === steps.length" @click="processPayment"
                                            :disabled="!isFormValid || isProcessing"
                                            :style="getButtonClasses.customStyle"
                                            class="flex-1 h-12 sm:h-14 text-white font-bold rounded-lg sm:rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 sm:space-x-3 shadow-lg hover:shadow-xl disabled:shadow-none disabled:cursor-not-allowed transform hover:scale-[1.02] disabled:transform-none text-sm sm:text-base hover:opacity-90">
                                            <Loader2 v-if="isProcessing" class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" />
                                            <ArrowRight v-else class="w-4 h-4 sm:w-5 sm:h-5" />
                                            <span>{{ isProcessing ? 'Processando Pagamento...' : 'Finalizar Compra'
                                            }}</span>
                                        </button>
                                    </div>

                                    <!-- Security Info -->
                                    <div class="mt-4 sm:mt-6 text-center">
                                        <div class="flex items-center justify-center space-x-2"
                                            :class="checkout.dark_mode ? 'text-gray-400' : 'text-gray-500'">
                                            <ShieldCheck class="w-3 h-3 sm:w-4 sm:h-4" />
                                            <span class="text-xs sm:text-sm">Seus dados estão protegidos com
                                                criptografia
                                                SSL</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Melhorias para touch em dispositivos móveis */
.touch-manipulation {
    touch-action: manipulation;
}

/* Melhor foco em mobile */
*:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Melhor touch target size */
button,
input,
select,
textarea {
    min-height: 44px;
}

/* Melhor espaçamento para checkboxes e radios */
input[type="checkbox"],
input[type="radio"] {
    min-width: 20px;
    min-height: 20px;
}

/* Melhor scroll em mobile */
::-webkit-scrollbar {
    width: 4px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 2px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.3);
}

/* Prevenir zoom em inputs no iOS */
@media screen and (-webkit-min-device-pixel-ratio: 0) {

    input,
    select,
    textarea {
        font-size: 16px;
    }
}

/* Melhor contraste para botões em mobile */
button {
    -webkit-tap-highlight-color: transparent;
}

/* Melhor feedback visual para toques */
* {
    -webkit-tap-highlight-color: rgba(59, 130, 246, 0.1);
}

/* Melhor espaçamento para elementos interativos */
button,
input,
select,
textarea,
[role="button"] {
    margin: 0.25rem 0;
}

/* Melhor legibilidade em telas pequenas */
@media (max-width: 640px) {
    .text-xs {
        font-size: 0.75rem;
        line-height: 1rem;
    }

    .text-sm {
        font-size: 0.875rem;
        line-height: 1.25rem;
    }
}
</style>

<style scoped>
/* Enhanced animations and transitions */
.peer:checked~label .w-3 {
    opacity: 1;
}

input:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

button:not(:disabled):hover {
    transform: scale(1.02);
}

button:not(:disabled):active {
    transform: scale(0.98);
}

/* Smooth transitions for all interactive elements */
* {
    transition: all 0.2s ease;
}

/* Payment Method Selection Enhancements */
.payment-method-option {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.payment-method-option:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.payment-method-option:active {
    transform: translateY(0);
}

/* Check mark animation */
.check-mark-overlay {
    animation: checkMarkPulse 2s ease-in-out infinite;
}

@keyframes checkMarkPulse {

    0%,
    100% {
        transform: scale(1);
        opacity: 1;
    }

    50% {
        transform: scale(1.1);
        opacity: 0.8;
    }
}

/* Selected state glow effect */
.payment-method-selected {
    box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
}

/* Radio button custom styling */
.payment-radio:checked+label {
    border-color: var(--selected-color);
    background: var(--selected-bg);
    box-shadow: 0 0 20px var(--selected-shadow);
}

/* Credit card fields animation */
.credit-card-fields {
    animation: slideInFromTop 0.5s ease-out;
}

@keyframes slideInFromTop {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Field animation delays */
.field-animation-1 {
    animation-delay: 0.1s;
}

.field-animation-2 {
    animation-delay: 0.2s;
}

.field-animation-3 {
    animation-delay: 0.3s;
}

.field-animation-4 {
    animation-delay: 0.4s;
}

/* Hover effects for payment icons */
.payment-icon {
    transition: all 0.3s ease;
}

.payment-icon:hover {
    transform: scale(1.05);
    filter: brightness(1.1);
}

/* Selected payment method badge */
.selected-badge {
    animation: badgePop 0.3s ease-out;
}

@keyframes badgePop {
    0% {
        transform: scale(0);
        opacity: 0;
    }

    50% {
        transform: scale(1.2);
    }

    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Order Bump specific styles */
.order-bump-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 2px dashed #fbbf24;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.order-bump-card:hover {
    border-color: #f59e0b;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    transform: translateY(-2px);
}

.order-bump-card.dark {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-color: #fbbf24;
}

.order-bump-card.dark:hover {
    border-color: #f59e0b;
}

/* Order Bump Selection States */
.order-bump-selected {
    border-color: #10b981 !important;
    border-style: solid !important;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%) !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1), 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
    transform: translateY(-1px) scale(1.01) !important;
}

.order-bump-selected.dark {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 100%) !important;
    border-color: #10b981 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2), 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2) !important;
}

/* Order Bump Animation States */
.order-bump-animating {
    animation: orderBumpPulse 0.5s ease-in-out;
}

.order-bump-pulse {
    animation: orderBumpPulse 0.3s ease-in-out;
}

@keyframes orderBumpPulse {
    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.02);
    }

    100% {
        transform: scale(1);
    }
}

/* Checkbox Animation States */
.checkbox-selected {
    animation: checkboxPop 0.3s ease-out;
}

.checkbox-pulse {
    animation: checkboxPulse 0.3s ease-in-out;
}

@keyframes checkboxPop {
    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.2);
    }

    100% {
        transform: scale(1);
    }
}

@keyframes checkboxPulse {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }

    50% {
        transform: scale(1.1);
        box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
    }

    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
    }
}

/* Price Animation States */
.price-selected {
    color: #10b981 !important;
    font-weight: 900 !important;
    text-shadow: 0 0 10px rgba(16, 185, 129, 0.3) !important;
}

.price-bounce {
    animation: priceBounce 0.4s ease-out;
}

@keyframes priceBounce {
    0% {
        transform: scale(1);
    }

    25% {
        transform: scale(1.1);
    }

    50% {
        transform: scale(0.95);
    }

    75% {
        transform: scale(1.05);
    }

    100% {
        transform: scale(1);
    }
}

/* Enhanced hover effects for order bump cards */
.order-bump-card:hover .gift-emoji {
    animation: giftBounce 0.6s ease-in-out infinite;
}

@keyframes giftBounce {

    0%,
    100% {
        transform: translateY(0) rotate(0deg);
    }

    25% {
        transform: translateY(-3px) rotate(-5deg);
    }

    75% {
        transform: translateY(-3px) rotate(5deg);
    }
}

/* Improved focus states for accessibility */
.order-bump-card:focus-within {
    outline: 2px solid #10b981;
    outline-offset: 2px;
}

/* Enhanced visual feedback for selection */
.order-bump-card input[type="checkbox"]:checked+label {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
}

/* Smooth transitions for all order bump elements */
.order-bump-card * {
    transition: all 0.2s ease;
}

/* Dark mode enhancements for selected state */
.dark .order-bump-selected {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 100%) !important;
    border-color: #10b981 !important;
}

.dark .price-selected {
    color: #34d399 !important;
    text-shadow: 0 0 10px rgba(52, 211, 153, 0.4) !important;
}

/* Total price animation */
@keyframes totalUpdate {
    0% {
        transform: scale(1);
        color: inherit;
    }

    50% {
        transform: scale(1.05);
        color: #10b981;
    }

    100% {
        transform: scale(1);
        color: inherit;
    }
}

.total-price {
    transition: all 0.3s ease;
}

/* Enhanced order bump summary */
.order-bump-summary {
    animation: slideInFromTop 0.3s ease-out;
}

@keyframes slideInFromTop {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Check icon animation */
@keyframes checkPop {
    0% {
        transform: scale(0) rotate(-180deg);
        opacity: 0;
    }

    50% {
        transform: scale(1.2) rotate(0deg);
        opacity: 1;
    }

    100% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
}

.check-icon-animate {
    animation: checkPop 0.4s ease-out;
}

/* Custom checkbox animation */
.order-bump-checkbox {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.order-bump-checkbox:checked {
    animation: pulse 0.3s ease-in-out;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.1);
    }

    100% {
        transform: scale(1);
    }
}

/* Gradient text effect for price */
.price-gradient {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Star icon animation */
.star-icon {
    animation: twinkle 2s ease-in-out infinite;
}

@keyframes twinkle {

    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }

    50% {
        opacity: 0.8;
        transform: scale(1.1);
    }
}

/* Gift emoji bounce */
.gift-emoji {
    animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {

    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-2px);
    }
}

/* Dark mode specific enhancements */
.dark .payment-method-option {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-color: #374151;
}

.dark .payment-method-option:hover {
    border-color: var(--hover-color);
    background: linear-gradient(135deg, #1f2937 0%, #1e293b 100%);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
}

.dark .payment-method-option:active {
    transform: translateY(0);
    box-shadow: 0 5px 15px -5px rgba(0, 0, 0, 0.4);
}

/* Dark mode selected state */
.dark .payment-method-selected {
    box-shadow: 0 0 20px var(--selected-shadow-dark);
    background: linear-gradient(135deg, var(--selected-bg-dark) 0%, var(--selected-bg-dark-secondary) 100%);
}

/* Dark mode payment icons */
.dark .payment-icon {
    filter: brightness(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.dark .payment-icon:hover {
    filter: brightness(1.2);
    transform: scale(1.05);
}

/* Dark mode check mark */
.dark .check-mark-overlay {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
}

/* Dark mode selected badge */
.dark .selected-badge {
    background: var(--badge-bg-dark);
    color: var(--badge-text-dark);
    border: 1px solid var(--badge-border-dark);
}

/* Dark mode radio buttons */
.dark .payment-radio:checked+label .w-2.5 {
    background: #ffffff;
}

/* Dark mode text colors */
.dark .payment-method-option h4 {
    color: #f9fafb;
}

.dark .payment-method-option p {
    color: #d1d5db;
}

/* Dark mode hover effects */
.dark .payment-method-option:hover h4 {
    color: var(--hover-text-dark);
}

.dark .payment-method-option:hover p {
    color: var(--hover-text-secondary-dark);
}

/* CSS Variables for dark mode */
:root {
    --hover-color: #4b5563;
    --selected-shadow-dark: rgba(59, 130, 246, 0.4);
    --selected-bg-dark: rgba(59, 130, 246, 0.1);
    --selected-bg-dark-secondary: rgba(59, 130, 246, 0.05);
    --badge-bg-dark: rgba(59, 130, 246, 0.2);
    --badge-text-dark: #dbeafe;
    --badge-border-dark: rgba(59, 130, 246, 0.3);
    --hover-text-dark: #ffffff;
    --hover-text-secondary-dark: #e5e7eb;
}

/* PIX specific dark mode */
.dark .payment-method-option[data-method="pix"]:hover {
    border-color: #10b981;
}

.dark .payment-method-option[data-method="pix"].selected {
    --selected-shadow-dark: rgba(16, 185, 129, 0.4);
    --selected-bg-dark: rgba(16, 185, 129, 0.1);
    --selected-bg-dark-secondary: rgba(16, 185, 129, 0.05);
    --badge-bg-dark: rgba(16, 185, 129, 0.2);
    --badge-text-dark: #d1fae5;
    --badge-border-dark: rgba(16, 185, 129, 0.3);
}

/* Credit Card specific dark mode */
.dark .payment-method-option[data-method="credit_card"]:hover {
    border-color: #3b82f6;
}

.dark .payment-method-option[data-method="credit_card"].selected {
    --selected-shadow-dark: rgba(59, 130, 246, 0.4);
    --selected-bg-dark: rgba(59, 130, 246, 0.1);
    --selected-bg-dark-secondary: rgba(59, 130, 246, 0.05);
    --badge-bg-dark: rgba(59, 130, 246, 0.2);
    --badge-text-dark: #dbeafe;
    --badge-border-dark: rgba(59, 130, 246, 0.3);
}

/* Boleto specific dark mode */
.dark .payment-method-option[data-method="boleto"]:hover {
    border-color: #f59e0b;
}

.dark .payment-method-option[data-method="boleto"].selected {
    --selected-shadow-dark: rgba(245, 158, 11, 0.4);
    --selected-bg-dark: rgba(245, 158, 11, 0.1);
    --selected-bg-dark-secondary: rgba(245, 158, 11, 0.05);
    --badge-bg-dark: rgba(245, 158, 11, 0.2);
    --badge-text-dark: #fef3c7;
    --badge-border-dark: rgba(245, 158, 11, 0.3);
}
</style>