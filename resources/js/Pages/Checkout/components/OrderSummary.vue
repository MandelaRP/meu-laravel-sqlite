<script setup>
import { computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { 
  Package, 
  Tag, 
  CreditCard, 
  Truck, 
  Shield, 
  Clock,
  CheckCircle,
  Receipt
} from 'lucide-vue-next'

const props = defineProps({
  checkout: {
    type: Object,
    required: true
  },
  selectedPaymentMethod: {
    type: String,
    default: ''
  },
  formData: {
    type: Object,
    default: () => ({})
  },
  selectedOrderBumps: {
    type: Set,
    default: () => new Set()
  }
})

// Computed para calcular preços
const subtotal = computed(() => {
  const productPrice = parseFloat(props.checkout.product.price)
  const orderBumpsPrice = props.checkout.order_bumps.reduce((total, bump) => {
    // Só incluir se o order bump estiver selecionado
    if (props.selectedOrderBumps.has(bump.id)) {
      return total + parseFloat(bump.product.price)
    }
    return total
  }, 0)
  return productPrice + orderBumpsPrice
})

const discountAmount = computed(() => {
  if (!props.checkout.discount_percentage) return 0
  return (subtotal.value * props.checkout.discount_percentage) / 100
})

const total = computed(() => {
  return subtotal.value - discountAmount.value
})

// Função para formatar preço
const formatPrice = (price) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(price)
}

// Computed para order bumps selecionados
const selectedOrderBumpsList = computed(() => {
  return props.checkout.order_bumps.filter(bump => props.selectedOrderBumps.has(bump.id))
})

// Função para verificar se há order bumps selecionados
const hasSelectedOrderBumps = computed(() => {
  return selectedOrderBumpsList.value.length > 0
})

// Função para obter informações de entrega
const getDeliveryInfo = () => {
  const hasPhysicalProduct = props.checkout.product.type === 'FISICAL' || 
    selectedOrderBumpsList.value.some(bump => bump.product.type === 'FISICAL')
  
  if (hasPhysicalProduct) {
    return {
      type: 'FISICAL',
      description: 'Entrega em 5-7 dias úteis',
      icon: Truck,
      color: '#10B981'
    }
  } else {
    return {
      type: 'DIGITAL',
      description: 'Acesso imediato',
      icon: Package,
      color: '#8B5CF6'
    }
  }
}

// Função para obter informações do método de pagamento
const getPaymentMethodInfo = () => {
  const method = props.checkout.payment_methods.find(m => m.name === props.selectedPaymentMethod)
  if (!method) return null
  
  switch (method.name) {
    case 'pix':
      return {
        description: 'Pagamento instantâneo',
        color: '#00D4AA'
      }
    case 'credit_card':
      return {
        description: 'Pague em até 12x',
        color: '#2563EB'
      }
    case 'boleto':
      return {
        description: 'Vencimento em 3 dias',
        color: '#F59E0B'
      }
    default:
      return null
  }
}
</script>

<template>
  <Card class="w-full shadow-lg border-0 rounded-2xl bg-white">
    <CardHeader class="pb-4 bg-white">
      <CardTitle class="flex items-center gap-2 text-lg text-gray-900">
        <Receipt class="h-5 w-5 text-gray-900" />
        Resumo do Pedido
      </CardTitle>
    </CardHeader>
    
    <CardContent class="space-y-4 bg-white text-gray-900">
      <!-- Produto Principal -->
      <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
        <img 
          :src="`/storage/${checkout.product.image}`" 
          :alt="checkout.product.name"
          class="w-12 h-12 object-cover rounded-lg"
        />
        <div class="flex-1 min-w-0">
          <h4 class="font-semibold text-sm mb-1 truncate text-gray-900">
            {{ checkout.product.name }}
          </h4>
          <div class="flex items-center justify-between">
            <!-- <Badge variant="outline" class="text-xs">
              {{ checkout.product.type === 'DIGITAL' ? 'Digital' : 'Físico' }}
            </Badge> -->
            <span class="font-semibold text-sm text-gray-900">
              {{ formatPrice(checkout.product.price) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Order Bumps -->
      <div v-if="hasSelectedOrderBumps" class="space-y-2">
        <h5 class="font-medium text-gray-700 text-sm">Produtos Adicionais</h5>
        <div 
          v-for="bump in selectedOrderBumpsList" 
          :key="bump.id"
          class="flex items-start gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200"
        >
          <img 
            :src="`/storage/${bump.product.image}`" 
            :alt="bump.product.name"
            class="w-10 h-10 object-cover rounded-lg"
          />
          <div class="flex-1 min-w-0">
            <h6 class="font-medium text-gray-900 text-sm mb-1 truncate">
              {{ bump.product.name }}
            </h6>
            <div class="flex items-center justify-between">
              <!-- <Badge variant="outline" class="text-xs">
                {{ bump.product.type === 'DIGITAL' ? 'Digital' : 'Físico' }}
              </Badge> -->
              <span class="font-semibold text-gray-900 text-sm">
                {{ formatPrice(bump.product.price) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Resumo de Preços -->
      <div class="space-y-2 border-t pt-4">
        <div class="flex justify-between text-sm">
          <span class="text-gray-600">Subtotal</span>
          <span class="font-medium text-gray-900">{{ formatPrice(subtotal) }}</span>
        </div>
        
        <div v-if="discountAmount > 0" class="flex justify-between text-sm">
          <span class="text-gray-600 flex items-center gap-1">
            <Tag class="h-3 w-3 text-green-600" />
            Desconto ({{ checkout.discount_percentage }}%)
          </span>
          <span class="font-medium text-blue-600">-{{ formatPrice(discountAmount) }}</span>
        </div>

        <div class="flex justify-between text-lg font-semibold border-t pt-2">
          <span class="text-gray-900">Total</span>
          <span class="text-green-600">{{ formatPrice(total) }}</span>
        </div>
      </div>

      <!-- Informações de Entrega -->
      <div v-show="false" class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
        <component 
          :is="getDeliveryInfo().icon" 
          class="h-4 w-4"
          :style="{ color: getDeliveryInfo().color }"
        />
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-900">
            {{ getDeliveryInfo().type === 'DIGITAL' ? 'Entrega Digital' : 'Entrega Física' }}
          </p>
          <p class="text-xs text-gray-600">
            {{ getDeliveryInfo().description }}
          </p>
        </div>
      </div>

      <!-- Método de Pagamento Selecionado -->
      <div v-if="selectedPaymentMethod && getPaymentMethodInfo()" class="flex items-center gap-2 p-3 bg-blue-50 rounded-lg border border-blue-200">
        <CreditCard class="h-4 w-4" :style="{ color: getPaymentMethodInfo().color }" />
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-900">
            {{ checkout.payment_methods.find(m => m.name === selectedPaymentMethod)?.label }}
          </p>
          <p class="text-xs text-gray-600">
            {{ getPaymentMethodInfo().description }}
          </p>
        </div>
      </div>

      <!-- Garantias e Segurança -->
      <div v-show="false" class="grid grid-cols-1 gap-2">
        <div class="flex items-center gap-2 text-xs text-gray-600">
          <Shield class="h-3 w-3 text-green-600" />
          <span>Pagamento 100% seguro</span>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-600">
          <CheckCircle class="h-3 w-3 text-blue-600" />
          <span>Garantia de 30 dias</span>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-600">
          <Clock class="h-3 w-3 text-orange-600" />
          <span>Suporte 24/7</span>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<style scoped>
/* Truncate para textos longos */
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Smooth transitions */
* {
  transition: all 0.2s ease-in-out;
}

/* Hover effects */
.hover-lift:hover {
  transform: translateY(-1px);
}

/* Animation for new items */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.slide-in {
  animation: slideIn 0.3s ease-out;
}
</style> 