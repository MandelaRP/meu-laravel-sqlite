<template>
  <Card>
    <CardHeader class="px-3 md:px-6">
      <CardTitle class="flex items-center gap-2 text-sm md:text-base">
        <LayoutGrid class="h-4 w-4 md:h-5 md:w-5" />
        Aparência
      </CardTitle>
      <CardDescription class="text-xs md:text-sm">
        Configure a aparência do seu checkout
      </CardDescription>
    </CardHeader>
    <CardContent class="px-3 md:px-6">
      <div class="space-y-4 md:space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
          <div class="space-y-2">
            <Label for="layout" class="text-xs md:text-sm">Layout</Label>
            <Select v-model="form.layout" id="layout">
              <SelectTrigger class="text-xs md:text-sm">
                <SelectValue placeholder="Selecione um layout" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Layout</SelectLabel>
                  <SelectItem value="single" class="text-xs md:text-sm">Uma coluna</SelectItem>
                  <SelectItem value="two-column" class="text-xs md:text-sm">Duas colunas</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
            <InputError :message="form.errors.layout" />
            <p class="text-xs text-muted-foreground mt-1">
              O layout de uma coluna é ideal para dispositivos móveis, enquanto o de duas colunas funciona
              melhor em desktops.
            </p>
          </div>

          <div class="space-y-2">
            <Label class="text-xs md:text-sm">Banner</Label>
            <ImageUpload 
              label="Banner" 
              v-model="form.banner" 
              :preview="bannerPreview"
              placeholder="Arraste uma imagem ou clique para fazer upload" 
            />
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { LayoutGrid } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'
import ImageUpload from '@/components/ImageUpload.vue'

defineProps({
  form: Object,
  bannerPreview: String,
})
</script> 