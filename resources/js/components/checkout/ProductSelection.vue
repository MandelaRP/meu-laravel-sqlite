<template>
  <Card>
    <CardHeader class="px-3 md:px-6">
      <CardTitle class="flex items-center gap-2 text-sm md:text-base">
        <Tag class="h-4 w-4 md:h-5 md:w-5" />
        Produto Principal
      </CardTitle>
      <CardDescription class="text-xs md:text-sm">
        Selecione o produto principal que será vendido neste checkout
      </CardDescription>
    </CardHeader>
    <CardContent class="px-3 md:px-6">
      <div class="space-y-3 md:space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
          <div>
            <Label for="product" class="text-xs md:text-sm">Produto</Label>
            <Select v-model="form.product_id" id="product">
              <SelectTrigger class="w-full text-xs md:text-sm">
                <SelectValue placeholder="Selecione um produto" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Produtos</SelectLabel>
                  <SelectItem v-for="product in products" :value="product.id" :key="product.id" class="text-xs md:text-sm">
                    {{ product.name }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
            <InputError :message="form.errors.product_id" />
          </div>
          
          <div>
            <Label for="discount_percentage" class="text-xs md:text-sm">Desconto (%)</Label>
            <Input 
              v-model="form.discount_percentage" 
              id="discount_percentage" 
              type="number" 
              min="0" 
              max="100" 
              placeholder="0"
              class="w-full text-xs md:text-sm" 
            />
            <InputError :message="form.errors.discount_percentage" />
            <p class="text-xs text-muted-foreground mt-1">
              Desconto em porcentagem aplicado ao produto
            </p>
          </div>
        </div>

        <div v-if="selectedProduct" class="mt-3 md:mt-4 p-3 md:p-4 bg-muted rounded-lg flex items-center gap-3 md:gap-4">
          <div class="flex-shrink-0">
            <div v-if="selectedProduct.image" class="w-12 h-12 md:w-16 md:h-16 rounded-md overflow-hidden">
              <img :src="`/storage/${selectedProduct.image}`" :alt="selectedProduct.name"
                class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-12 h-12 md:w-16 md:h-16 rounded-md bg-background border flex items-center justify-center">
              <Box class="w-4 h-4 md:w-6 md:h-6 text-muted-foreground" />
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-medium text-sm md:text-base truncate">{{ selectedProduct.name }}</h3>
            <p v-if="selectedProduct.price" class="text-xs md:text-sm text-muted-foreground">
              Preço: <span class="text-green-600 font-medium">R$ {{ formatPrice(selectedProduct.price) }}</span>
            </p>
            <p v-if="form.discount_percentage" class="text-xs md:text-sm text-muted-foreground">
              Desconto: <span class="text-green-600 font-medium">{{ form.discount_percentage }}%</span>
            </p>
            <p v-if="form.discount_percentage" class="text-xs md:text-sm text-muted-foreground">
              Preço com desconto: <span class="text-green-600 font-medium">R$ {{ formatPrice(selectedProduct.price * (1 - form.discount_percentage / 100)) }}</span>
            </p>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { Tag, Box } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'

defineProps({
  form: Object,
  products: Array,
  selectedProduct: Object,
  formatPrice: Function,
})
</script> 