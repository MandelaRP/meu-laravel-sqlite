
<script setup>
import { computed } from 'vue'
import { Check } from 'lucide-vue-next'

const props = defineProps({
    method: {
        type: String,
        required: true
    },
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        required: true
    },
    icon: {
        type: Object,
        default: null
    },
    iconSrc: {
        type: String,
        default: ''
    },
    isImageIcon: {
        type: Boolean,
        default: false
    },
    modelValue: {
        type: String,
        required: true
    },
    colors: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['update:modelValue'])

const isSelected = computed(() => props.modelValue === props.method)

// Computed properties for dynamic classes
const borderColor = computed(() => props.colors.border)
const bgColor = computed(() => props.colors.bg)
const shadowColor = computed(() => props.colors.shadow)
const iconBgColor = computed(() => props.colors.iconBg)
const checkMarkColor = computed(() => props.colors.checkMark)
const checkIconColor = computed(() => {
  console.log('=== PAYMENT METHOD OPTION CHECK DEBUG ===');
  console.log('Method:', props.method);
  console.log('Colors object:', props.colors);
  console.log('CheckIconColor value:', props.colors.checkIconColor);
  console.log('Final checkIconColor:', props.colors.checkIconColor || '#ffffff');
  console.log('Is selected:', props.modelValue === props.method);
  console.log('=== END PAYMENT METHOD OPTION CHECK DEBUG ===');
  return props.colors.checkIconColor || '#ffffff';
})
const titleSelectedColor = computed(() => props.colors.titleSelected)
const titleDefaultColor = computed(() => props.colors.titleDefault)
const descriptionSelectedColor = computed(() => props.colors.descriptionSelected)
const descriptionDefaultColor = computed(() => props.colors.descriptionDefault)
const radioSelectedColor = computed(() => props.colors.radioSelected)
const radioDefaultColor = computed(() => props.colors.radioDefault)
const badgeColor = computed(() => props.colors.badge)
const hoverBorder = computed(() => props.colors.hoverBorder)
const hoverBg = computed(() => props.colors.hoverBg)
const defaultBorder = computed(() => props.colors.defaultBorder)
const defaultBg = computed(() => props.colors.defaultBg)
</script>
<template>
    <div class="relative">
        <input type="radio" :id="method" :checked="modelValue === method" :value="method"
            @change="emit('update:modelValue', method)" class="sr-only peer payment-radio" />
        <label :for="method"
            class="payment-method-option flex items-center p-4 sm:p-6 border-2 rounded-lg sm:rounded-xl cursor-pointer transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
            :class="isSelected
                ? `${borderColor} ${bgColor} ${shadowColor}`
                : `${defaultBorder} ${hoverBorder} ${hoverBg} ${defaultBg}`">
            <div class="flex items-center space-x-3 sm:space-x-4 flex-1">
                <div class="relative">
                    <div class="payment-icon w-12 h-12 sm:w-16 sm:h-16 rounded-lg sm:rounded-xl flex items-center justify-center text-white font-bold text-md shadow-lg"
                        :style="`background-color: ${iconBgColor || '#10b981'} !important;`">
                        <component :is="icon" v-if="!isImageIcon" class="w-6 h-6 sm:w-8 sm:h-8" />
                        <img v-else :src="iconSrc" :alt="title" class="w-8 h-8 sm:w-10 sm:h-10" />
                    </div>
                    <!-- Check mark overlay -->
                    <div v-if="isSelected"
                        class="check-mark-overlay absolute -top-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center animate-pulse"
                        :style="`background-color: ${checkIconColor} !important; border: 2px solid ${checkIconColor} !important;`"
                        :title="`Check background color: ${checkIconColor}`">
                        <Check class="w-3 h-3 text-white" />
                        <!-- Debug: mostrar a cor atual -->
                        <div v-if="false" class="absolute -top-8 left-0 bg-black text-white text-xs px-1 py-0.5 rounded">
                            {{ checkIconColor }}
                        </div>
                    </div>
                    <!-- Debug: mostrar a cor atual em texto -->
                    <div v-if="isSelected" class="absolute -top-8 left-0 bg-black text-white text-xs px-1 py-0.5 rounded">
                        {{ checkIconColor }}
                    </div>
                    <!-- Teste temporário: forçar cor vermelha para debug -->
                    <div v-if="isSelected && false"
                        class="absolute -top-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center animate-pulse bg-red-500"
                        title="Teste: cor vermelha forçada">
                        <Check class="w-3 h-3 text-white" />
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold mb-1 text-sm sm:text-base flex items-center"
                        :class="isSelected ? titleSelectedColor : titleDefaultColor">
                        {{ title }}
                        <!-- <span v-if="isSelected" class="selected-badge ml-2 text-xs px-2 py-1 rounded-full"
                            :class="badgeColor">
                            Selecionado
                        </span> -->
                    </h4>
                    <p class="text-xs sm:text-sm mb-2"
                        :class="isSelected ? descriptionSelectedColor : descriptionDefaultColor">
                        {{ description }}
                    </p>
                </div>
                <div class="w-5 h-5 sm:w-6 sm:h-6 border-2 rounded-full flex items-center justify-center transition-all duration-300 flex-shrink-0"
                    :class="isSelected ? radioSelectedColor : radioDefaultColor">
                    <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 bg-white rounded-full transition-all duration-300"
                        :class="isSelected ? 'scale-100' : 'scale-0'">
                    </div>
                </div>
            </div>
        </label>
    </div>
</template>


<style scoped>
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
    box-shadow: var(--selected-shadow);
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
</style>