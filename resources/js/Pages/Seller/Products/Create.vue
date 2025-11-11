<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Switch } from '@/components/ui/switch'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import ImageUpload from '@/components/ImageUpload.vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import { Package } from 'lucide-vue-next'

const breadcrumbs = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Produtos',
    href: '/seller/products',
  },
  {
    title: 'Novo Produto',
    href: '/seller/products/create',
  },
]

const form = useForm({
  name: '',
  description: '',
  type: 'DIGITAL',
  price: '',
  image: null,
})

const submit = () => {
  // Converter o preço para número antes de enviar
  const priceValue = form.price ? parseFloat(form.price.toString().replace(',', '.')) : 0
  
  if (isNaN(priceValue) || priceValue < 0) {
    toast.error('Por favor, insira um preço válido')
    return
  }

  form.transform((data) => ({
    ...data,
    price: priceValue,
    status: true, // Todos os produtos criados são ativos por padrão
  })).post(route('products.store'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Produto criado com sucesso!')
    },
    onError: (errors) => {
      if (errors.message) {
        toast.error(errors.message)
      } else if (errors.price) {
        toast.error(errors.price[0] || 'Erro ao validar preço')
      } else {
        toast.error('Erro ao criar produto. Verifique os campos.')
      }
    },
  })
}

const formatPrice = (value) => {
  if (!value) return ''
  
  // Remove tudo que não é número, vírgula ou ponto
  let cleaned = value.toString().replace(/[^\d,.]/g, '')
  
  // Se não tem nada, retorna vazio
  if (!cleaned) return ''
  
  // Substitui vírgula por ponto para processamento interno
  cleaned = cleaned.replace(',', '.')
  
  // Remove múltiplos pontos, mantendo apenas o primeiro
  const parts = cleaned.split('.')
  if (parts.length > 2) {
    cleaned = parts[0] + '.' + parts.slice(1).join('')
  }
  
  // Limita a 2 casas decimais
  if (parts.length === 2 && parts[1].length > 2) {
    cleaned = parts[0] + '.' + parts[1].substring(0, 2)
  }
  
  // Converte de volta para vírgula para exibição (formato brasileiro)
  return cleaned.replace('.', ',')
}

const handlePriceInput = (event) => {
  const formatted = formatPrice(event.target.value)
  form.price = formatted
}
</script>

<template>
  <Head title="Criar Produto" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 w-full max-w-3xl mx-auto">
      <Card class="w-full border-border bg-card">
        <CardHeader>
          <CardTitle class="text-2xl font-bold text-card-foreground flex items-center gap-2">
            <Package class="h-6 w-6" />
            Novo Produto
          </CardTitle>
          <CardDescription class="text-base mt-1 text-muted-foreground">
            Preencha os dados do produto abaixo
          </CardDescription>
        </CardHeader>

        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Nome do Produto -->
            <div class="space-y-2">
              <Label for="name">
                Nome do Produto <span class="text-destructive">*</span>
              </Label>
              <Input
                id="name"
                v-model="form.name"
                placeholder="Digite o nome do produto"
                :class="{ 'border-destructive': form.errors.name }"
              />
              <p v-if="form.errors.name" class="text-sm text-destructive">
                {{ form.errors.name }}
              </p>
            </div>

            <!-- Descrição -->
            <div class="space-y-2">
              <Label for="description">Descrição</Label>
              <Textarea
                id="description"
                v-model="form.description"
                placeholder="Descreva seu produto ou serviço"
                rows="4"
                :class="{ 'border-destructive': form.errors.description }"
              />
              <p v-if="form.errors.description" class="text-sm text-destructive">
                {{ form.errors.description }}
              </p>
            </div>

            <!-- Tipo -->
            <div class="space-y-2">
              <Label for="type">
                Tipo <span class="text-destructive">*</span>
              </Label>
              <Select v-model="form.type" :class="{ 'border-destructive': form.errors.type }">
                <SelectTrigger id="type">
                  <SelectValue placeholder="Selecione o tipo" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="DIGITAL">Digital</SelectItem>
                  <SelectItem value="FISICAL">Físico</SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.type" class="text-sm text-destructive">
                {{ form.errors.type }}
              </p>
            </div>

            <!-- Preço -->
            <div class="space-y-2">
              <Label for="price">
                Preço <span class="text-destructive">*</span>
              </Label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-sm font-semibold text-foreground">R$</span>
                <Input
                  id="price"
                  v-model="form.price"
                  type="text"
                  placeholder="0,00"
                  @input="handlePriceInput"
                  class="pl-12"
                  :class="{ 'border-destructive': form.errors.price }"
                />
              </div>
              <p v-if="form.errors.price" class="text-sm text-destructive">
                {{ Array.isArray(form.errors.price) ? form.errors.price[0] : form.errors.price }}
              </p>
              <p class="text-xs text-muted-foreground">
                Digite o preço usando vírgula ou ponto como separador decimal (ex: 99,90 ou 99.90)
              </p>
            </div>

            <!-- Imagem do Produto -->
            <div class="space-y-2">
              <Label>Imagem do Produto</Label>
              <ImageUpload
                v-model="form.image"
                label="Imagem"
                placeholder="Clique para fazer upload ou arraste uma imagem"
                :max-size="5120"
              />
              <p v-if="form.errors.image" class="text-sm text-destructive">
                {{ form.errors.image }}
              </p>
            </div>

            <!-- Botões -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t">
              <Button
                type="button"
                variant="outline"
                @click="$inertia.visit(route('products.index'))"
                class="w-full sm:w-auto"
              >
                Cancelar
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
                class="w-full sm:w-auto"
              >
                {{ form.processing ? 'Criando...' : 'Criar Produto' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

