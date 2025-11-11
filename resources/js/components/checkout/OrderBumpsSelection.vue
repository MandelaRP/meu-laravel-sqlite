<template>
  <Card v-if="form.product_id">
    <CardHeader class="px-3 md:px-6">
      <CardTitle class="flex items-center gap-2 text-sm md:text-base">
        <ChevronRight class="h-4 w-4 md:h-5 md:w-5" />
        Produtos Adicionais (Order Bumps)
      </CardTitle>
      <CardDescription class="text-xs md:text-sm">
        Selecione produtos adicionais que serão oferecidos durante o checkout
      </CardDescription>
    </CardHeader>
    <CardContent class="px-3 md:px-6">
      <div v-if="availableProducts.length > 0" class="space-y-3 md:space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
          <div v-for="product in availableProducts" :key="product.id"
            class="flex items-start space-x-2 md:space-x-3 p-3 md:p-4 border rounded-lg hover:bg-muted/50 transition-colors">
            <div class="flex items-center h-5 mt-1">
              <input :id="`product-${product.id}`" v-model="form.order_bump_ids" type="checkbox"
                :value="product.id" name="order_bump_ids[]"
                class="h-4 w-4 text-primary focus:ring-primary border-input rounded" />
            </div>
            <div class="flex items-center gap-2 md:gap-3 flex-1 min-w-0">
              <div class="flex-shrink-0">
                <img v-if="product.image" :src="`/storage/${product.image}`" :alt="product.name"
                  class="w-8 h-8 md:w-12 md:h-12 rounded-md object-cover border" />
                <div v-else class="w-8 h-8 md:w-12 md:h-12 rounded-md bg-muted border flex items-center justify-center">
                  <Box class="w-4 h-4 md:w-5 md:h-5 text-muted-foreground" />
                </div>
              </div>

              <div class="flex-1 min-w-0">
                <label :for="`product-${product.id}`" class="font-medium text-foreground cursor-pointer text-xs md:text-sm truncate">
                  {{ product.name }}
                </label>
                <div v-if="product.price" class="text-xs md:text-sm font-medium text-green-600 mt-1">
                  R$ {{ formatPrice(product.price) }}
                </div>
              </div>
            </div>
          </div>
        </div>
        <InputError :message="form.errors.order_bump_ids" />
      </div>

      <div v-else class="flex flex-col items-center justify-center py-6 md:py-10 text-center">
        <Box class="w-8 h-8 md:w-12 md:h-12 text-muted-foreground/40 mb-2 md:mb-3" />
        <p class="text-xs md:text-sm text-muted-foreground">Nenhum produto adicional disponível</p>
        <p class="text-xs text-muted-foreground/70 mt-1">Adicione mais produtos ao seu catálogo para
          oferecê-los como order bumps</p>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { ChevronRight, Box } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import InputError from '@/components/InputError.vue'

defineProps({
  form: Object,
  availableProducts: Array,
  formatPrice: Function,
})
</script> 