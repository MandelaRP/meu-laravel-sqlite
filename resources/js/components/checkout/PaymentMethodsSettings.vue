<template>
  <Card>
    <CardHeader class="px-3 md:px-6">
      <CardTitle class="flex items-center gap-2 text-sm md:text-base">
        <LayoutGrid class="h-4 w-4 md:h-5 md:w-5" />
        Métodos de Pagamento
      </CardTitle>
      <CardDescription class="text-xs md:text-sm">
        Configure quais métodos de pagamento estarão disponíveis no checkout
      </CardDescription>
    </CardHeader>
    <CardContent class="px-3 md:px-6">
      <div class="space-y-4 md:space-y-6">
        <div class="space-y-3 md:space-y-4">
          <h4 class="font-medium text-xs md:text-sm">Métodos Disponíveis</h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
            <div v-for="method in form.payment_methods" :key="method.name"
              class="flex items-center space-x-2 md:space-x-3 p-2 md:p-3 border rounded-lg hover:bg-muted/50 transition-colors">
              <div class="flex items-center h-5">
                <input :id="`method-${method.name}`" 
                  :checked="method.enabled === true || method.enabled === '1' || method.enabled === 1"
                  @change="method.enabled = $event.target.checked"
                  type="checkbox"
                  class="h-4 w-4 text-primary focus:ring-primary border-input rounded" />
              </div>
              <div class="flex items-center gap-2 md:gap-3 flex-1 min-w-0">
                <div class="flex-shrink-0">
                  <div v-if="method.show_image === true || method.show_image === '1' || method.show_image === 1"
                    class="w-8 h-8 md:w-12 md:h-12 rounded-md flex items-center justify-center text-white text-xs md:text-sm font-medium"
                    :style="{ backgroundColor: method.icon_bg_color }">
                    <img :src="method.image" alt="Icone do método de pagamento" class="w-6 h-6 md:w-8 md:h-8" />
                  </div>
                  <div v-else
                    class="w-8 h-8 md:w-12 md:h-12 rounded-md flex items-center justify-center text-white text-xs md:text-sm font-medium"
                    :style="{ backgroundColor: method.icon_bg_color }">
                    <CreditCard v-if="method.icon === 'credit_card'" class="w-6 h-6 md:w-8 md:h-8" />
                    <Barcode v-if="method.icon === 'boleto'" class="w-6 h-6 md:w-8 md:h-8" />
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <label :for="`method-${method.name}`"
                    class="font-medium text-foreground cursor-pointer capitalize text-xs md:text-sm">
                    {{ method.label }}
                  </label>
                  <p class="text-xs text-muted-foreground truncate">
                    {{ method.description }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <Separator />

        <div class="space-y-3 md:space-y-4">
          <h4 class="font-medium text-xs md:text-sm">Cor de fundo dos Ícones</h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
            <div v-for="method in form.payment_methods" :key="method.name" class="space-y-2">
              <Label :for="`icon-${method.name}`" class="text-xs md:text-sm capitalize">
                {{ method.label }}
              </Label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="method.icon_bg_color" :id="`icon-${method.name}`"
                  class="w-6 h-6 md:w-8 md:h-8 rounded-md border border-input cursor-pointer" />
                <Input v-model="method.icon_bg_color" class="flex-1 text-xs" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { LayoutGrid, CreditCard, Barcode } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Separator } from '@/components/ui/separator'

defineProps({
  form: Object,
})
</script> 