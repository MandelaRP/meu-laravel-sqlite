<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { 
  Search, 
  Filter, 
  Plus, 
  MoreHorizontal, 
  Edit, 
  Trash2, 
  Package,
  CheckCircle,
  XCircle,
  Image as ImageIcon
} from 'lucide-vue-next'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import { Pagination } from '@/components/ui/pagination'

const props = defineProps({
  products: {
    type: Object,
    default: () => ({
      data: [],
      links: [],
      meta: {}
    })
  },
  filters: {
    type: Object,
    default: () => ({
      search: '',
      type: 'all',
      status: 'all'
    })
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
]

// Estado reativo
const searchTerm = ref(props.filters.search || '')
const typeFilter = ref(props.filters.type || 'all')
const statusFilter = ref(props.filters.status || 'all')
const showDeleteDialog = ref(false)
const productToDelete = ref(null)

// Funções utilitárias
const formatPrice = (price) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(price)
}

const getStatusConfig = (status) => {
  return status ? {
    color: 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800',
    label: 'Ativo',
    icon: CheckCircle
  } : {
    color: 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800',
    label: 'Inativo',
    icon: XCircle
  }
}

const getTypeLabel = (type) => {
  return type === 'DIGITAL' ? 'Digital' : 'Físico'
}

const getTypeColor = (type) => {
  return type === 'DIGITAL' 
    ? 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800'
    : 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-400 dark:border-yellow-800'
}

// Dados paginados
const productsList = computed(() => props.products.data || [])

// Filtrar produtos (filtro local apenas para busca rápida, filtros principais são no backend)
const filteredProducts = computed(() => {
  let filtered = [...productsList.value]

  // Filtro de busca
  if (searchTerm.value) {
    const search = searchTerm.value.toLowerCase()
    filtered = filtered.filter(product => 
      product.name.toLowerCase().includes(search) ||
      (product.description && product.description.toLowerCase().includes(search))
    )
  }

  // Filtro de tipo
  if (typeFilter.value !== 'all') {
    filtered = filtered.filter(product => product.type === typeFilter.value)
  }

  // Filtro de status
  if (statusFilter.value !== 'all') {
    const status = statusFilter.value === 'active'
    filtered = filtered.filter(product => product.status === status)
  }

  return filtered
})

// Funções de ação
const applyFilters = () => {
  router.get(route('products.index'), {
    search: searchTerm.value || null,
    type: typeFilter.value !== 'all' ? typeFilter.value : null,
    status: statusFilter.value !== 'all' ? statusFilter.value : null,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const confirmDelete = (product) => {
  productToDelete.value = product
  showDeleteDialog.value = true
}

const deleteProduct = () => {
  if (!productToDelete.value) return

  router.delete(route('products.destroy', productToDelete.value.id), {
    onSuccess: () => {
      toast.success('Produto excluído com sucesso!')
      showDeleteDialog.value = false
      productToDelete.value = null
    },
    onError: () => {
      toast.error('Erro ao excluir produto')
    },
  })
}

const toggleStatus = (product) => {
  // Determinar o status atual (pode vir como boolean, 1/0, ou '1'/'0')
  const currentStatus = product.status === true || product.status === 1 || product.status === '1' || product.status === 'true'
  const newStatus = !currentStatus
  
  const form = useForm({
    name: product.name,
    description: product.description || '',
    type: product.type,
    price: parseFloat(product.price),
    status: newStatus,
    _method: 'POST',
  })

  form.post(route('products.update', product.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success(`Produto ${newStatus ? 'ativado' : 'desativado'} com sucesso!`)
    },
    onError: (errors) => {
      console.error('Erro ao atualizar status:', errors)
      if (errors.status) {
        toast.error(errors.status[0] || 'Erro ao atualizar status do produto')
      } else {
        toast.error('Erro ao atualizar status do produto')
      }
    },
  })
}
</script>

<template>
  <Head title="Produtos" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 w-full max-w-7xl mx-auto">
      <Card class="w-full border-border bg-card">
        <CardHeader class="pb-4">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <CardTitle class="text-2xl font-bold text-card-foreground flex items-center gap-2">
                <Package class="h-6 w-6" />
                Produtos
              </CardTitle>
              <CardDescription class="text-base mt-1 text-muted-foreground">
                Gerencie seus produtos e serviços
              </CardDescription>
            </div>
            <Button @click="router.visit(route('products.create'))" size="sm"
              class="bg-primary text-primary-foreground hover:bg-primary/90 w-full sm:w-auto">
              <Plus class="h-4 w-4 mr-2" />
              Novo Produto
            </Button>
          </div>

          <!-- Filtros -->
          <div class="flex flex-col sm:flex-row gap-4 mt-6">
            <div class="flex-1">
              <div class="relative">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  v-model="searchTerm"
                  placeholder="Buscar produtos..."
                  class="pl-10 w-full"
                  @keyup.enter="applyFilters"
                />
              </div>
            </div>
            <Select v-model="typeFilter" @update:model-value="applyFilters">
              <SelectTrigger class="w-full sm:w-[180px]">
                <SelectValue placeholder="Todos os tipos" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Todos os tipos</SelectItem>
                <SelectItem value="DIGITAL">Digital</SelectItem>
                <SelectItem value="FISICAL">Físico</SelectItem>
              </SelectContent>
            </Select>
            <Select v-model="statusFilter" @update:model-value="applyFilters">
              <SelectTrigger class="w-full sm:w-[180px]">
                <SelectValue placeholder="Todos os status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Todos os status</SelectItem>
                <SelectItem value="active">Ativo</SelectItem>
                <SelectItem value="inactive">Inativo</SelectItem>
              </SelectContent>
            </Select>
            <Button variant="outline" @click="applyFilters" class="w-full sm:w-auto">
              <Filter class="h-4 w-4 mr-2" />
              Filtros
            </Button>
          </div>
        </CardHeader>

        <CardContent>
          <!-- Lista de Produtos -->
          <div class="space-y-4">
            <div v-if="filteredProducts.length === 0" class="text-center py-12 border border-dashed rounded-lg">
              <Package class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
              <h3 class="text-lg font-medium mb-2">Nenhum produto encontrado</h3>
              <p class="text-sm text-muted-foreground mb-4">
                Comece criando seu primeiro produto para começar a vender
              </p>
              <Button @click="router.visit(route('products.create'))" variant="outline">
                <Plus class="h-4 w-4 mr-2" />
                Criar Produto
              </Button>
            </div>

            <div v-else>
              <div class="mb-4 text-sm text-muted-foreground">
                {{ filteredProducts.length }} produto(s) encontrado(s)
              </div>

              <!-- Tabela Desktop -->
              <div class="hidden md:block overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Produto</TableHead>
                      <TableHead>Preço</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Tipo</TableHead>
                      <TableHead class="text-right">Ações</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="product in filteredProducts" :key="product.id">
                      <TableCell>
                        <div class="flex items-center gap-3">
                          <div v-if="product.image" class="h-10 w-10 rounded-md overflow-hidden flex-shrink-0">
                            <img 
                              :src="`/storage/${product.image}`" 
                              :alt="product.name"
                              class="h-full w-full object-cover"
                            />
                          </div>
                          <div v-else class="h-10 w-10 rounded-md bg-muted flex items-center justify-center flex-shrink-0">
                            <ImageIcon class="h-5 w-5 text-muted-foreground" />
                          </div>
                          <div>
                            <div class="font-medium">{{ product.name }}</div>
                            <div v-if="product.description" class="text-sm text-muted-foreground line-clamp-1">
                              {{ product.description }}
                            </div>
                          </div>
                        </div>
                      </TableCell>
                      <TableCell class="font-medium">{{ formatPrice(product.price) }}</TableCell>
                      <TableCell>
                        <Badge :class="getStatusConfig(product.status).color" variant="outline">
                          <component :is="getStatusConfig(product.status).icon" class="h-3 w-3 mr-1" />
                          {{ getStatusConfig(product.status).label }}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <Badge :class="getTypeColor(product.type)" variant="outline">
                          {{ getTypeLabel(product.type) }}
                        </Badge>
                      </TableCell>
                      <TableCell class="text-right">
                        <DropdownMenu>
                          <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm">
                              <MoreHorizontal class="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem @click="router.visit(route('products.edit', product.id))">
                              <Edit class="h-4 w-4 mr-2" />
                              Editar
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="toggleStatus(product)">
                              <component :is="getStatusConfig(!product.status).icon" class="h-4 w-4 mr-2" />
                              {{ product.status ? 'Desativar' : 'Ativar' }}
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="confirmDelete(product)" class="text-destructive">
                              <Trash2 class="h-4 w-4 mr-2" />
                              Excluir
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>

              <!-- Cards Mobile -->
              <div class="md:hidden space-y-4">
                <Card v-for="product in filteredProducts" :key="product.id">
                  <CardContent class="p-4">
                    <div class="flex items-start gap-3 mb-3">
                      <div v-if="product.image" class="h-16 w-16 rounded-md overflow-hidden flex-shrink-0">
                        <img 
                          :src="`/storage/${product.image}`" 
                          :alt="product.name"
                          class="h-full w-full object-cover"
                        />
                      </div>
                      <div v-else class="h-16 w-16 rounded-md bg-muted flex items-center justify-center flex-shrink-0">
                        <ImageIcon class="h-8 w-8 text-muted-foreground" />
                      </div>
                      <div class="flex-1 min-w-0">
                        <h3 class="font-medium truncate">{{ product.name }}</h3>
                        <p v-if="product.description" class="text-sm text-muted-foreground line-clamp-2 mt-1">
                          {{ product.description }}
                        </p>
                      </div>
                    </div>
                    <div class="space-y-2">
                      <div class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">Preço:</span>
                        <span class="font-medium">{{ formatPrice(product.price) }}</span>
                      </div>
                      <div class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">Status:</span>
                        <Badge :class="getStatusConfig(product.status).color" variant="outline">
                          <component :is="getStatusConfig(product.status).icon" class="h-3 w-3 mr-1" />
                          {{ getStatusConfig(product.status).label }}
                        </Badge>
                      </div>
                      <div class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">Tipo:</span>
                        <Badge :class="getTypeColor(product.type)" variant="outline">
                          {{ getTypeLabel(product.type) }}
                        </Badge>
                      </div>
                      <div class="flex gap-2 pt-2 border-t">
                        <Button 
                          variant="outline" 
                          size="sm" 
                          class="flex-1"
                          @click="router.visit(route('products.edit', product.id))"
                        >
                          <Edit class="h-4 w-4 mr-2" />
                          Editar
                        </Button>
                        <Button 
                          variant="outline" 
                          size="sm"
                          @click="toggleStatus(product)"
                        >
                          <component :is="getStatusConfig(!product.status).icon" class="h-4 w-4" />
                        </Button>
                        <Button 
                          variant="outline" 
                          size="sm"
                          @click="confirmDelete(product)"
                          class="text-destructive hover:text-destructive"
                        >
                          <Trash2 class="h-4 w-4" />
                        </Button>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
      
      <!-- Paginação -->
      <div v-if="products && ((products.meta && products.meta.total > 10 && products.meta.last_page > 1) || (products.total > 10 && products.last_page > 1))" class="mt-4">
        <Pagination :pagination="products" />
      </div>

      <!-- Dialog de Confirmação de Exclusão -->
      <Dialog v-model:open="showDeleteDialog">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Confirmar Exclusão</DialogTitle>
            <DialogDescription class="text-sm">
              Tem certeza que deseja excluir o produto "{{ productToDelete?.name }}"? 
              Esta ação não pode ser desfeita.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter class="flex-col sm:flex-row gap-2">
            <Button variant="outline" @click="showDeleteDialog = false" class="w-full sm:w-auto">
              Cancelar
            </Button>
            <Button variant="destructive" @click="deleteProduct" class="w-full sm:w-auto">
              Excluir Produto
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>

