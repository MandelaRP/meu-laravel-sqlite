<script setup>
import { ref, watch } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import ImageUpload from '@/components/ImageUpload.vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import { Package } from 'lucide-vue-next'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

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
    title: 'Editar Produto',
    href: `/seller/products/${props.product.id}/edit`,
  },
]

const form = useForm({
  name: props.product.name || '',
  description: props.product.description || '',
  type: props.product.type || 'DIGITAL',
  price: props.product.price ? parseFloat(props.product.price).toFixed(2).replace('.', ',') : '',
  image: null, // Sempre iniciar como null, o preview será usado para mostrar a imagem existente
  _method: 'POST',
})

const submit = () => {
  // Converter o preço para número antes de enviar
  const priceValue = form.price ? parseFloat(form.price.toString().replace(',', '.')) : 0
  
  if (isNaN(priceValue) || priceValue < 0) {
    toast.error('Por favor, insira um preço válido')
    return
  }

  // Preparar dados para envio
  const submitData = {
    name: form.name,
    description: form.description,
    type: form.type,
    price: priceValue,
  }

  // Gerenciar a imagem apenas se foi alterada
  if (form.image instanceof File) {
    // Nova imagem selecionada - adicionar ao submitData
    submitData.image = form.image
  } else if (imageRemoved.value && props.product.image) {
    // Imagem foi explicitamente removida pelo usuário - enviar 'remove' para o backend
    submitData.image = 'remove'
  }
  // Se image é null mas não foi explicitamente removida, não enviar o campo (mantém a imagem existente)

  form.transform(() => submitData).post(route('products.update', props.product.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Produto atualizado com sucesso!')
    },
    onError: (errors) => {
      if (errors.message) {
        toast.error(errors.message)
      } else if (errors.price) {
        toast.error(errors.price[0] || 'Erro ao validar preço')
      } else {
        toast.error('Erro ao atualizar produto. Verifique os campos.')
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

// Rastrear se a imagem foi explicitamente removida pelo usuário
const imageRemoved = ref(false)
// Flag para saber se já inicializou (evitar marcar como removida na inicialização)
const isInitialized = ref(false)

const handleImageChange = (value) => {
  // Se já inicializou e havia uma imagem no produto e o valor mudou para null, foi removida
  if (isInitialized.value && value === null && props.product.image) {
    // O componente ImageUpload emite null quando o botão de remover é clicado
    imageRemoved.value = true
  } else if (value instanceof File) {
    // Nova imagem selecionada, resetar flag de remoção
    imageRemoved.value = false
    isInitialized.value = true
  } else {
    // Marcar como inicializado após a primeira mudança
    if (!isInitialized.value) {
      isInitialized.value = true
    }
  }
}
</script>

<template>
  <Head title="Editar Produto" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 w-full max-w-3xl mx-auto">
      <Card class="w-full border-border bg-card">
        <CardHeader>
          <CardTitle class="text-2xl font-bold text-card-foreground flex items-center gap-2">
            <Package class="h-6 w-6" />
            Editar Produto
          </CardTitle>
          <CardDescription class="text-base mt-1 text-muted-foreground">
            Atualize os dados do produto abaixo
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
                :preview="product.image"
                label="Imagem"
                placeholder="Clique para fazer upload ou arraste uma imagem"
                :max-size="5120"
                @update:model-value="handleImageChange"
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
                {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

