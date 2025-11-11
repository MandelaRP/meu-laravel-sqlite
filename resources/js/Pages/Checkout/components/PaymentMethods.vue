<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
  CreditCard,
  QrCode,
  FileText,
  CheckCircle,
  Shield,
  Clock,
  Zap
} from 'lucide-vue-next'

const props = defineProps({
  paymentMethods: {
    type: Array,
    default: () => []
  },
  selectedMethod: {
    type: String,
    default: ''
  },
  showRecommended: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['method-selected'])

// Computed para métodos habilitados
const enabledMethods = computed(() => {
  return props.paymentMethods.filter(method => method.enabled === true || method.enabled === "1")
})

// Computed para método recomendado (primeiro método habilitado)
const recommendedMethod = computed(() => {
  return enabledMethods.value[0] || null
})

// Estado local para método selecionado - pré-selecionar o primeiro método habilitado
const selectedMethod = ref(props.selectedMethod || '')

// Garantir que o primeiro método seja selecionado ao montar o componente ou quando métodos mudarem
onMounted(() => {
  if (!selectedMethod.value && recommendedMethod.value) {
    selectedMethod.value = recommendedMethod.value.name
    emit('method-selected', recommendedMethod.value.name)
  }
})

// Observar mudanças no selectedMethod do pai e atualizar localmente
watch(() => props.selectedMethod, (newValue) => {
  if (newValue && newValue !== selectedMethod.value) {
    selectedMethod.value = newValue
  } else if (!newValue && recommendedMethod.value && !selectedMethod.value) {
    // Se não houver método selecionado do pai, selecionar o primeiro disponível
    selectedMethod.value = recommendedMethod.value.name
    emit('method-selected', recommendedMethod.value.name)
  }
}, { immediate: true })

// Observar mudanças nos métodos habilitados para pré-selecionar
watch(enabledMethods, (newMethods) => {
  if (newMethods.length > 0 && !selectedMethod.value) {
    selectedMethod.value = newMethods[0].name
    emit('method-selected', newMethods[0].name)
  }
}, { immediate: true })

// Função para selecionar método
const selectMethod = (methodName) => {
  selectedMethod.value = methodName
  emit('method-selected', methodName)
}

// Função para obter ícone do método
const getMethodIcon = (method) => {
  switch (method.icon) {
    case 'pix':
      return QrCode
    case 'credit_card':
      return CreditCard
    case 'boleto':
      return FileText
    default:
      return CreditCard
  }
}

// Função para obter informações do método
const getMethodInfo = (method) => {
  switch (method.name) {
    case 'pix':
      return {
        description: 'Pagamento instantâneo',
        benefits: ['Pagamento instantâneo', 'Sem taxas', 'QR Code'],
        icon: Zap,
        color: '#00D4AA'
      }
    case 'credit_card':
      return {
        description: '',
        benefits: ['Parcelamento', 'Pontos no cartão', 'Seguro'],
        icon: Shield,
        color: '#2563EB'
      }
    case 'boleto':
      return {
        description: '',
        benefits: ['Sem juros', 'Vencimento flexível', 'Bancário'],
        icon: Clock,
        color: '#F59E0B'
      }
    default:
      return {
        description: 'Método de pagamento',
        benefits: [],
        icon: CreditCard,
        color: '#6B7280'
      }
  }
}

// Função para verificar se método é recomendado
const isRecommended = (method) => {
  return method.name === recommendedMethod.value?.name
}
</script>

<template>
  <div class="space-y-4">
    <!-- Título da Seção -->
    <div class=" space-y-2">
      <h3 class="text-lg font-semibold" :style="{ color: 'inherit' }">
        Escolha a forma de pagamento
      </h3>
      <!-- <p class="text-sm text-gray-600">
        Selecione a opção que melhor se adapta a você
      </p> -->
    </div>

    <!-- Métodos de Pagamento -->
    <div class="grid gap-3">
      <div v-for="method in enabledMethods" :key="method.name" @click="selectMethod(method.name)"
        class="relative cursor-pointer transition-all duration-200" :class="{
          'transform scale-[1.02]': selectedMethod === method.name,
          'hover:scale-[1.01]': selectedMethod !== method.name
        }">
        <Card class="border-2 transition-all duration-200 overflow-hidden rounded-xl bg-white" :class="{
          'border-blue-500 bg-blue-50/30 shadow-lg': selectedMethod === method.name,
          'border-gray-200 hover:border-gray-300 hover:shadow-md bg-white': selectedMethod !== method.name
        }">
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <!-- Informações do Método -->
              <div class="flex items-center gap-4 flex-1">
                <!-- Ícone/Imagem -->
                <div class="w-12 h-12 rounded-lg flex items-center justify-center relative"
                  :style="{ backgroundColor: method.icon_bg_color }">
                  <!-- Imagem do método -->
                  <img v-if="method.show_image === '1' || method.show_image === true" :src="method.image"
                    :alt="method.label" class="w-8 h-8 object-contain" />
                  <!-- Ícone padrão -->
                  <component v-else :is="getMethodIcon(method)" class="w-6 h-6" :style="{ color: method.icon_color }" />
                </div>

                <!-- Detalhes do Método -->
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <h4 class="font-semibold text-gray-900">
                      {{ method.label }}
                    </h4>

                 
                  </div>

                  <p class="text-sm text-gray-600">
                    {{ getMethodInfo(method).description }}
                  </p>
                </div>
              </div>

              <!-- Seleção Visual -->
              <div class="flex items-center gap-3">
                <!-- Radio Button Customizado -->
                <div class="relative">
                  <div class="w-5 h-5 rounded-full border-2 transition-all duration-200" :class="{
                    'border-blue-500 bg-blue-500': selectedMethod === method.name,
                    'border-gray-300': selectedMethod !== method.name
                  }">
                    <div v-if="selectedMethod === method.name" class="absolute inset-1 bg-white rounded-full" />
                  </div>
                </div>
              </div>
            </div>


          </CardContent>
        </Card>

        <!-- Indicador de Seleção -->
        <div v-if="selectedMethod === method.name"
          class="absolute -top-1 -right-1 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
          <CheckCircle class="w-4 h-4 text-white" />
        </div>
      </div>
    </div>

    <!-- Mensagem de Segurança -->
    <div class="text-center">
      <div class="inline-flex items-center gap-2 text-xs opacity-60" :style="{ color: 'inherit' }">
        <Shield class="w-4 h-4" />
        <span>Pagamento 100% seguro</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Animações suaves */
.card-enter-active,
.card-leave-active {
  transition: all 0.3s ease;
}

.card-enter-from,
.card-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

/* Hover effects */
.hover-lift {
  transition: transform 0.2s ease;
}

.hover-lift:hover {
  transform: translateY(-2px);
}

/* Pulse animation for recommended badge */
@keyframes pulse-green {

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.8;
  }
}

.bg-green-100 {
  animation: pulse-green 2s infinite;
}
</style>