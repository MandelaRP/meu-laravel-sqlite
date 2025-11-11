<script setup>
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Loader2, CheckCircle, AlertCircle, ShoppingCart } from 'lucide-vue-next'

const props = defineProps({
  // Estados do botão
  isSubmitting: {
    type: Boolean,
    default: false
  },
  isValid: {
    type: Boolean,
    default: false
  },
  hasData: {
    type: Boolean,
    default: false
  },
  submitSuccess: {
    type: Boolean,
    default: false
  },
  submitError: {
    type: String,
    default: ''
  },
  
  // Configurações visuais
  primaryColor: {
    type: String,
    default: '#2563eb'
  },
  hoverColor: {
    type: String,
    default: '#1d4ed8'
  },
  
  // Textos customizáveis
  loadingText: {
    type: String,
    default: 'Processando...'
  },
  successText: {
    type: String,
    default: 'Pedido Enviado!'
  },
  defaultText: {
    type: String,
    default: 'Finalizar Compra'
  },
  emptyText: {
    type: String,
    default: 'Preencha os dados'
  },
  errorText: {
    type: String,
    default: 'Por favor, preencha todos os campos obrigatórios.'
  }
})

const emit = defineEmits(['click'])

// Computed para texto dinâmico
const buttonText = computed(() => {
  if (props.isSubmitting) return props.loadingText
  if (props.submitSuccess) return props.successText
  return props.hasData ? props.defaultText : props.emptyText
})

// Computed para ícone
const buttonIcon = computed(() => {
  if (props.isSubmitting) return Loader2
  if (props.submitSuccess) return CheckCircle
  return ShoppingCart
})

// Computed para classes do botão
const buttonClasses = computed(() => {
  return {
    'opacity-50 cursor-not-allowed': props.isSubmitting || !props.isValid,
    'transform scale-95': props.isSubmitting,
    'shadow-lg hover:shadow-xl': !props.isSubmitting && props.isValid,
    'success-bounce': props.submitSuccess
  }
})

// Computed para estilo do botão
const buttonStyle = computed(() => {
  return {
    backgroundColor: props.isSubmitting ? props.primaryColor + '80' : props.primaryColor,
    '--hover-color': props.hoverColor
  }
})

// Função para lidar com o clique
const handleClick = () => {
  if (!props.isSubmitting && props.isValid) {
    emit('click')
  }
}
</script>

<template>
  <div class="space-y-3">
    <!-- Botão Principal -->
    <Button 
      @click="handleClick" 
      :disabled="isSubmitting || !isValid"
      class="w-full h-14 text-lg font-semibold transition-all duration-300 relative overflow-hidden group"
      :class="buttonClasses"
      :style="buttonStyle"
    >
      <!-- Loading State -->
      <div v-if="isSubmitting" class="flex items-center justify-center gap-3">
        <Loader2 class="h-5 w-5 animate-spin" />
        <span>{{ buttonText }}</span>
      </div>
      
      <!-- Success State -->
      <div v-else-if="submitSuccess" class="flex items-center justify-center gap-3">
        <CheckCircle class="h-5 w-5" />
        <span>{{ buttonText }}</span>
      </div>
      
      <!-- Default State -->
      <div v-else class="flex items-center justify-center gap-3">
        <component :is="buttonIcon" class="h-5 w-5" />
        <span>{{ buttonText }}</span>
      </div>
      
      <!-- Hover Effect -->
      <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
    </Button>
    
    <!-- Error Message -->
    <div v-if="submitError" class="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-lg">
      <AlertCircle class="h-4 w-4 text-red-500" />
      <span class="text-sm text-red-700">{{ submitError }}</span>
    </div>
    
    <!-- Success Message -->
    <div v-if="submitSuccess" class="flex items-center gap-2 p-3 bg-green-50 border border-green-200 rounded-lg">
      <CheckCircle class="h-4 w-4 text-green-500" />
      <span class="text-sm text-green-700">{{ successText }}</span>
    </div>
    
    <!-- Validation Error -->
    <div v-if="!isValid && hasData" class="flex items-center gap-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
      <AlertCircle class="h-4 w-4 text-yellow-500" />
      <span class="text-sm text-yellow-700">{{ errorText }}</span>
    </div>
  </div>
</template>

<style scoped>
/* Custom hover effect for the button */
.group:hover {
  background-color: var(--hover-color) !important;
}

/* Smooth transitions for all states */
* {
  transition: all 0.3s ease-in-out;
}

/* Button animation on hover */
.group:hover:not(:disabled) {
  transform: translateY(-1px);
}

/* Loading animation */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Success animation */
@keyframes success-bounce {
  0%, 20%, 53%, 80%, 100% {
    transform: translate3d(0, 0, 0);
  }
  40%, 43% {
    transform: translate3d(0, -8px, 0);
  }
  70% {
    transform: translate3d(0, -4px, 0);
  }
  90% {
    transform: translate3d(0, -2px, 0);
  }
}

.success-bounce {
  animation: success-bounce 1s ease-in-out;
}

/* Disabled state */
.group:disabled {
  transform: none !important;
}

/* Focus state */
.group:focus-visible {
  outline: 2px solid var(--hover-color);
  outline-offset: 2px;
}
</style> 