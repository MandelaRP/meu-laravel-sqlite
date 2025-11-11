<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { Switch } from '@/components/ui/switch'

import {
  Search,
  Filter,
  Download,
  Plus,
  MoreHorizontal,
  Edit,
  Trash2,
  Tag,
  Package,
  Calendar,
  ArrowUpDown,
  ArrowUp,
  ArrowDown,
  Eye,
  Settings,
  Archive,
  CheckCircle,
  XCircle,
} from 'lucide-vue-next'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

const props = defineProps({
  categories: {
    type: Array,
    default: () => []
  }
})

// Estado reativo
const searchTerm = ref('')
const statusFilter = ref('all')
const sortField = ref(null)
const sortDirection = ref('desc')

// Estado para modais
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedCategory = ref(null)

const breadcrumbs = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Categorias',
    href: '/seller/categories',
  },
]

const user = usePage().props.auth.user;

// Formulários
const createForm = useForm({
  name: '',
  description: '',
  status: true
})

const editForm = useForm({
  name: '',
  description: '',
  status: true
})

// Funções utilitárias
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

// Funções de ordenação
const handleSort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'desc'
  }
}

const getSortIcon = (field) => {
  if (sortField.value !== field) {
    return ArrowUpDown
  }
  return sortDirection.value === 'asc' ? ArrowUp : ArrowDown
}

// Computed properties
const filteredAndSortedCategories = computed(() => {
  const filtered = props.categories.filter((category) => {
    const matchesSearch = category.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      (category.description && category.description.toLowerCase().includes(searchTerm.value.toLowerCase()))
    const matchesStatus = statusFilter.value === 'all' ||
      (statusFilter.value === 'active' && category.status) ||
      (statusFilter.value === 'inactive' && !category.status)

    return matchesSearch && matchesStatus
  })

  if (sortField.value) {
    filtered.sort((a, b) => {
      let aValue = a[sortField.value]
      let bValue = b[sortField.value]

      if (sortField.value === 'products_count') {
        aValue = (aValue || 0).toString()
        bValue = (bValue || 0).toString()
      }

      if (sortField.value === 'created_at') {
        aValue = new Date(aValue).getTime().toString()
        bValue = new Date(bValue).getTime().toString()
      }

      if (sortDirection.value === 'asc') {
        return aValue.localeCompare(bValue)
      } else {
        return bValue.localeCompare(aValue)
      }
    })
  }

  return filtered
})


const clearFilters = () => {
  searchTerm.value = ''
  statusFilter.value = 'all'
}

const hasActiveFilters = computed(() => {
  return searchTerm.value || statusFilter.value !== 'all'
})

// Funções dos modais
const openCreateModal = () => {
  createForm.reset()
  showCreateModal.value = true
}

const openEditModal = (category) => {
  selectedCategory.value = category
  editForm.name = category.name
  editForm.description = category.description || ''
  editForm.status = category.status
  showEditModal.value = true
}

const openDeleteModal = (category) => {
  selectedCategory.value = category
  showDeleteModal.value = true
}

// Funções CRUD
const createCategory = () => {
  createForm.post(route('categories.store', user.id), {
    errorBag: 'createForm',
    onSuccess: () => {
      showCreateModal.value = false
      createForm.reset()
      toast.success('Categoria criada com sucesso')
    },
    onError: (error) => {
      console.log(error);
      if (error.fatal) {
        toast.error(error.fatal[0])
      }
    }
  })
}

const updateCategory = () => {
  editForm.put(route('categories.update', selectedCategory.value.id), {
    errorBag: 'editForm',
    onSuccess: () => {
      showEditModal.value = false
      selectedCategory.value = null
      toast.info('Categoria atualizada com sucesso')
    },
    onError: () => {
      toast.error('Erro ao atualizar categoria')
    }
  })
}

const deleteCategory = () => {
  useForm().delete(route('categories.destroy', selectedCategory.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      selectedCategory.value = null
      toast.success('Categoria excluída com sucesso')
    },
    onError: (errors) => {
      console.log(errors);
      toast.error('Erro ao excluir categoria')
    }
  })
}
</script>

<template>

  <Head title="Categorias" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <TooltipProvider>
      <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 w-full max-w-7xl mx-auto">
        <Card class="w-full border-border bg-card">
          <CardHeader class="pb-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div>
                <CardTitle class="text-2xl font-bold text-card-foreground flex items-center gap-2">
                  <Tag class="h-6 w-6" />
                  Gerenciar Categorias
                </CardTitle>
                <CardDescription class="text-base mt-1 text-muted-foreground">
                  Organize seus produtos em categorias para melhor navegação
                </CardDescription>
              </div>
              <div class="flex items-center gap-2">
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button variant="outline" size="sm"
                      class="border-border hover:bg-accent hover:text-accent-foreground">
                      <Download class="h-4 w-4 mr-2" />
                      Exportar
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>Exportar lista de categorias</p>
                  </TooltipContent>
                </Tooltip>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button @click="openCreateModal" size="sm"
                      class="bg-primary text-primary-foreground hover:bg-primary/90">
                      <Plus class="h-4 w-4 mr-2" />
                      Nova Categoria
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>Criar nova categoria</p>
                  </TooltipContent>
                </Tooltip>
              </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-4 mt-6">
              <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                <Input v-model="searchTerm" placeholder="Buscar por nome ou descrição..."
                  class="pl-10 bg-background border-border text-foreground placeholder:text-muted-foreground" />
              </div>
              <Select v-model="statusFilter">
                <SelectTrigger class="w-full sm:w-[180px] bg-background border-border text-foreground">
                  <Filter class="h-4 w-4 mr-2 text-muted-foreground" />
                  <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent class="bg-popover border-border">
                  <SelectItem value="all" class="text-popover-foreground hover:bg-accent hover:text-accent-foreground">
                    Todos os Status</SelectItem>
                  <SelectItem value="active"
                    class="text-popover-foreground hover:bg-accent hover:text-accent-foreground">Ativo</SelectItem>
                  <SelectItem value="inactive"
                    class="text-popover-foreground hover:bg-accent hover:text-accent-foreground">Inativo</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </CardHeader>

          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow class="bg-muted/50 border-border hover:bg-muted/50">
                    <TableHead class="font-semibold text-muted-foreground">
                      <Button variant="ghost" size="sm" @click="handleSort('name')"
                        class="h-auto p-0 font-semibold hover:bg-transparent text-muted-foreground hover:text-foreground">
                        Categoria
                        <component :is="getSortIcon('name')" class="h-4 w-4 ml-1"
                          :class="sortField === 'name' ? 'text-foreground' : 'opacity-50'" />
                      </Button>
                    </TableHead>
                    <TableHead class="font-semibold text-muted-foreground">Descrição</TableHead>

                    <TableHead class="font-semibold text-muted-foreground">
                      <Button variant="ghost" size="sm" @click="handleSort('status')"
                        class="h-auto p-0 font-semibold hover:bg-transparent text-muted-foreground hover:text-foreground">
                        Status
                        <component :is="getSortIcon('status')" class="h-4 w-4 ml-1"
                          :class="sortField === 'status' ? 'text-foreground' : 'opacity-50'" />
                      </Button>
                    </TableHead>
                    <TableHead class="font-semibold text-muted-foreground">
                      <Button variant="ghost" size="sm" @click="handleSort('products_count')"
                        class="h-auto p-0 font-semibold hover:bg-transparent text-muted-foreground hover:text-foreground">
                        Produtos
                        <component :is="getSortIcon('products_count')" class="h-4 w-4 ml-1"
                          :class="sortField === 'products_count' ? 'text-foreground' : 'opacity-50'" />
                      </Button>
                    </TableHead>
                    <TableHead class="font-semibold text-muted-foreground">Ações</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="category in filteredAndSortedCategories" :key="category.id"
                    class="border-border hover:bg-muted/50 transition-colors">
                    <TableCell>
                      <div class="flex items-center gap-3">

                        <div>
                          <div class="font-medium text-foreground">{{ category.name }}</div>

                        </div>
                      </div>
                    </TableCell>
                    <TableCell class="text-foreground max-w-xs">
                      <div class="truncate">
                        {{ category.description || '-' }}
                      </div>
                    </TableCell>

                    <TableCell>
                      <Badge variant="outline" :class="`${getStatusConfig(category.status).color} font-medium`">
                        <component :is="getStatusConfig(category.status).icon" class="h-3 w-3 mr-1" />
                        {{ getStatusConfig(category.status).label }}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div class="flex items-center gap-2">
                        <Package class="h-4 w-4 text-muted-foreground" />
                        <span class="font-medium text-foreground">{{ category.products_count || 0 }}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div class="flex items-center gap-1">
                        <DropdownMenu>
                          <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm"
                              class="h-8 w-8 p-0 hover:bg-accent hover:text-accent-foreground">
                              <MoreHorizontal class="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end" class="bg-popover border-border">
                            <DropdownMenuItem @click="openEditModal(category)"
                              class="text-popover-foreground hover:bg-accent hover:text-accent-foreground">
                              <Edit class="mr-2 h-4 w-4" />
                              Editar
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="openDeleteModal(category)"
                              class="text-destructive hover:bg-destructive/10">
                              <Trash2 class="mr-2 h-4 w-4" />
                              Excluir
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>

            <!-- Empty State -->
            <div v-if="filteredAndSortedCategories.length === 0"
              class="flex flex-col items-center justify-center py-12 text-center">
              <div class="bg-muted rounded-full p-3 mb-4">
                <Tag class="h-6 w-6 text-muted-foreground" />
              </div>
              <h3 class="text-lg font-semibold text-foreground mb-2">Nenhuma categoria encontrada</h3>
              <p class="text-muted-foreground max-w-sm">
                {{
                  hasActiveFilters
                    ? 'Tente ajustar os filtros para encontrar as categorias desejadas.'
                    : 'Quando você criar categorias, elas aparecerão aqui.'
                }}
              </p>
              <div class="flex gap-2 mt-4">
                <Button v-if="hasActiveFilters" variant="outline" size="sm"
                  class="border-border hover:bg-accent hover:text-accent-foreground" @click="clearFilters">
                  Limpar filtros
                </Button>
                <Button v-if="!hasActiveFilters" @click="openCreateModal" size="sm"
                  class="bg-primary text-primary-foreground hover:bg-primary/90">
                  <Plus class="h-4 w-4 mr-2" />
                  Criar Primeira Categoria
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Modal de Criação -->
      <Dialog v-model:open="showCreateModal">
        <DialogContent class="sm:max-w-[425px] bg-card border-border">
          <DialogHeader>
            <DialogTitle class="text-card-foreground">Nova Categoria</DialogTitle>
            <DialogDescription class="text-muted-foreground">
              Crie uma nova categoria para organizar seus produtos
            </DialogDescription>
          </DialogHeader>


          <form @submit.prevent="createCategory" class="space-y-4">
            <div class="space-y-2">
              <Label for="name" class="text-foreground">Nome</Label>
              <Input id="name" v-model="createForm.name" placeholder="Digite o nome da categoria"
                class="bg-background border-border text-foreground"
                :class="{ 'border-destructive': createForm.errors.name }" />
              <p v-if="createForm.errors.name" class="text-sm text-destructive">
                {{ createForm.errors.name }}
              </p>
            </div>
            <div class="space-y-2">
              <Label for="description" class="text-foreground">Descrição</Label>
              <textarea id="description" v-model="createForm.description" placeholder="Digite uma descrição (opcional)"
                rows="3"
                class="w-full min-h-[80px] resize-none rounded-md border border-border bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" />
            </div>

            <div class="flex items-center space-x-2">
              <Switch id="status" v-model="createForm.status" />
              <Label for="status" class="text-foreground">Categoria ativa</Label>
            </div>
          </form>
          <DialogFooter>
            <Button variant="outline" @click="showCreateModal = false" class="border-border hover:bg-accent">
              Cancelar
            </Button>
            <Button @click="createCategory" :disabled="createForm.processing"
              class="bg-primary text-primary-foreground hover:bg-primary/90">
              {{ createForm.processing ? 'Criando...' : 'Criar Categoria' }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Modal de Edição -->
      <Dialog v-model:open="showEditModal">

        <DialogContent class="sm:max-w-[425px] bg-card border-border">
          <DialogHeader>
            <DialogTitle class="text-card-foreground">Editar Categoria</DialogTitle>
            <DialogDescription class="text-muted-foreground">
              Atualize as informações da categoria
            </DialogDescription>
          </DialogHeader>

          <form @submit.prevent="updateCategory" class="space-y-4">
            <div class="space-y-2">
              <Label for="edit-name" class="text-foreground">Nome</Label>
              <Input id="edit-name" v-model="editForm.name" placeholder="Digite o nome da categoria"
                class="bg-background border-border text-foreground"
                :class="{ 'border-destructive': editForm.errors.name }" />
              <p v-if="editForm.errors.name" class="text-sm text-destructive">
                {{ editForm.errors.name }}
              </p>
            </div>
            <div class="space-y-2">
              <Label for="edit-description" class="text-foreground">Descrição</Label>
              <textarea id="edit-description" v-model="editForm.description"
                placeholder="Digite uma descrição (opcional)" rows="3"
                class="w-full min-h-[80px] resize-none rounded-md border border-border bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" />
            </div>

            <div class="flex items-center space-x-2">
              <Switch id="edit-status" v-model="editForm.status" />
              <Label for="edit-status" class="text-foreground">Categoria ativa</Label>
            </div>
          </form>
          <DialogFooter>
            <Button variant="outline" @click="showEditModal = false" class="border-border hover:bg-accent">
              Cancelar
            </Button>
            <Button @click="updateCategory" :disabled="editForm.processing"
              class="bg-primary text-primary-foreground hover:bg-primary/90">
              {{ editForm.processing ? 'Salvando...' : 'Salvar Alterações' }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Modal de Exclusão -->
      <Dialog v-model:open="showDeleteModal">
        <DialogContent class="sm:max-w-[425px] bg-card border-border">
          <DialogHeader>
            <DialogTitle class="text-card-foreground">Confirmar Exclusão</DialogTitle>
            <DialogDescription class="text-muted-foreground">
              Tem certeza que deseja excluir a categoria "{{ selectedCategory?.name }}"?
              Esta ação não pode ser desfeita.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" @click="showDeleteModal = false" class="border-border hover:bg-accent">
              Cancelar
            </Button>
            <Button variant="destructive" @click="deleteCategory">
              Excluir Categoria
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </TooltipProvider>
  </AppLayout>
</template>
