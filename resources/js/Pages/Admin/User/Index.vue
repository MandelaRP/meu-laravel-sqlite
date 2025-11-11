<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import {
  Search,
  Filter,
  Download,
  Eye,
  Trash2,
  UserPlus,
  Users,
  DollarSign,
  Calendar,
  ArrowUpDown,
  ArrowUp,
  ArrowDown,
  Shield,
  User,
  Crown,
  Settings,
} from 'lucide-vue-next'
import { Pagination } from '@/components/ui/pagination'

const props = defineProps({
  name: String,
  users: {
    type: Object,
    default: () => ({
      data: [],
      links: [],
      meta: {}
    })
  },
  pendingUsers: {
    type: Number,
    default: 0,
  },
  filters: {
    type: Object,
    default: () => ({
      role: 'all'
    })
  },
})

// Estado reativo
const searchTerm = ref('')
const roleFilter = ref(props.filters?.role || 'all')
const sortField = ref(null)
const sortDirection = ref('desc')
const showDeleteDialog = ref(false)
const userToDelete = ref(null)

// Dados paginados
const usersList = computed(() => props.users.data || [])

const breadcrumbs = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Usuários',
    href: '/users',
  },
]

// Funções utilitárias
const getRoleConfig = (role) => {
  switch (role?.toLowerCase()) {
    case 'admin':
      return { 
        color: 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800', 
        label: 'Admin',
        icon: Crown
      }
    case 'manager':
      return { 
        color: 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800', 
        label: 'Gerente',
        icon: Shield
      }
    case 'user':
      return { 
        color: 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800', 
        label: 'Usuário',
        icon: User
      }
    default:
      return { 
        color: 'bg-muted text-muted-foreground border-border', 
        label: role || 'N/A',
        icon: User
      }
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatCurrency = (value) => {
  if (!value) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', { 
    style: 'currency', 
    currency: 'BRL' 
  }).format(parseFloat(value))
}

const parseBalance = (balance) => {
  return parseFloat(balance) || 0
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

// Função para confirmar exclusão de usuário
const confirmDelete = (user) => {
  userToDelete.value = user
  showDeleteDialog.value = true
}

// Função para excluir usuário
const deleteUser = () => {
  if (!userToDelete.value) return

  router.delete(route('users.destroy', userToDelete.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteDialog.value = false
      userToDelete.value = null
      // Mensagem de sucesso será exibida pelo backend via redirect
    },
    onError: (errors) => {
      const errorMessage = errors.message || errors.error || 'Erro ao excluir usuário. Tente novamente.';
      alert(errorMessage);
      showDeleteDialog.value = false
      userToDelete.value = null
    },
  })
}

// Função para aplicar filtros via backend
const applyFilters = () => {
  router.get(route('users.index'), {
    role: roleFilter.value !== 'all' ? roleFilter.value : null,
    search: searchTerm.value || null,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

// Computed properties
const filteredAndSortedUsers = computed(() => {
  const filtered = usersList.value.filter((user) => {
    const matchesSearch =
      user.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      user.email.toLowerCase().includes(searchTerm.value.toLowerCase())
    const matchesRole = roleFilter.value === 'all' || user.role?.toLowerCase() === roleFilter.value

    return matchesSearch && matchesRole
  })

  if (sortField.value) {
    filtered.sort((a, b) => {
      let aValue = a[sortField.value]
      let bValue = b[sortField.value]

      if (sortField.value === 'created_at') {
        aValue = new Date(aValue).getTime().toString()
        bValue = new Date(bValue).getTime().toString()
      }

      if (sortField.value === 'balance') {
        aValue = parseBalance(aValue).toString()
        bValue = parseBalance(bValue).toString()
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

const totalUsers = computed(() => props.users.meta?.total || props.users.data?.length || 0)

const totalBalance = computed(() => {
  return usersList.value.reduce((sum, user) => {
    return sum + parseBalance(user.balance)
  }, 0)
})

const adminCount = computed(() => {
  return usersList.value.filter(user => user.role?.toLowerCase() === 'admin').length
})

const clearFilters = () => {
  searchTerm.value = ''
  roleFilter.value = 'all'
}

const hasActiveFilters = computed(() => {
  return searchTerm.value || roleFilter.value !== 'all'
})

const getInitials = (name) => {
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

// Verificar mensagens de sucesso/erro do backend
onMounted(() => {
  const page = usePage()
  const flash = page.props.flash
  
  if (flash?.success) {
    toast.success(flash.success)
  }
  
  if (flash?.error) {
    toast.error(flash.error)
  }
  
  if (flash?.cancelled) {
    toast.info(flash.cancelled)
  }
})
</script>

<template>
  <Head title="Usuários" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <TooltipProvider>
      <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-7xl mx-auto w-full">
        <Card class="w-full border-border bg-card">
          <CardHeader class="pb-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div>
                <CardTitle class="text-2xl font-bold text-card-foreground flex items-center gap-2">
                  <Users class="h-6 w-6" />
                  Gerenciar Usuários
                </CardTitle>
                <CardDescription class="text-base mt-1 text-muted-foreground">
                  Visualize e gerencie todos os usuários do sistema
                </CardDescription>
              </div>
              <div class="flex items-center gap-2">
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button variant="outline" size="sm" class="border-border hover:bg-accent hover:text-accent-foreground">
                      <Download class="h-4 w-4 mr-2" />
                      Exportar
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>Exportar lista de usuários</p>
                  </TooltipContent>
                </Tooltip>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button size="sm" class="bg-primary text-primary-foreground hover:bg-primary/90">
                      <UserPlus class="h-4 w-4 mr-2" />
                      Novo Usuário
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>Adicionar novo usuário</p>
                  </TooltipContent>
                </Tooltip>
              </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
              <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-950/50 dark:to-blue-900/50 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Total de Usuários</p>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                      {{ totalUsers }}
                    </p>
                  </div>
                  <Users class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                </div>
              </div>
              <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-950/50 dark:to-green-900/50 p-4 rounded-lg border border-green-200 dark:border-green-800">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-green-700 dark:text-green-300">Saldo Total</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                      {{ formatCurrency(totalBalance) }}
                    </p>
                  </div>
                  <DollarSign class="h-8 w-8 text-green-600 dark:text-green-400" />
                </div>
              </div>
              <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-950/50 dark:to-purple-900/50 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-purple-700 dark:text-purple-300">Administradores</p>
                    <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ adminCount }}</p>
                  </div>
                  <Crown class="h-8 w-8 text-purple-600 dark:text-purple-400" />
                </div>
              </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-4 mt-6">
              <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                <Input
                  v-model="searchTerm"
                  placeholder="Buscar por nome ou email..."
                  class="pl-10 bg-background border-border text-foreground placeholder:text-muted-foreground"
                />
              </div>
              <Select v-model="roleFilter">
                <SelectTrigger class="w-full sm:w-[180px] bg-background border-border text-foreground">
                  <Filter class="h-4 w-4 mr-2 text-muted-foreground" />
                  <SelectValue placeholder="Cargo" />
                </SelectTrigger>
                <SelectContent class="bg-popover border-border">
                  <SelectItem value="all" class="text-popover-foreground hover:bg-accent hover:text-accent-foreground">Todos os Cargos</SelectItem>
                  <SelectItem value="admin" class="text-popover-foreground hover:bg-accent hover:text-accent-foreground">Admin</SelectItem>
                  <SelectItem value="manager" class="text-popover-foreground hover:bg-accent hover:text-accent-foreground">Gerente</SelectItem>
                  <SelectItem value="user" class="text-popover-foreground hover:bg-accent hover:text-accent-foreground">Usuário</SelectItem>
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
                      <Button
                        variant="ghost"
                        size="sm"
                        @click="handleSort('name')"
                        class="h-auto p-0 font-semibold hover:bg-transparent text-muted-foreground hover:text-foreground"
                      >
                        Usuário
                        <component :is="getSortIcon('name')" class="h-4 w-4 ml-1" :class="sortField === 'name' ? 'text-foreground' : 'opacity-50'" />
                      </Button>
                    </TableHead>
                    <TableHead class="font-semibold text-muted-foreground">
                      <Button
                        variant="ghost"
                        size="sm"
                        @click="handleSort('email')"
                        class="h-auto p-0 font-semibold hover:bg-transparent text-muted-foreground hover:text-foreground"
                      >
                        Email
                        <component :is="getSortIcon('email')" class="h-4 w-4 ml-1" :class="sortField === 'email' ? 'text-foreground' : 'opacity-50'" />
                      </Button>
                    </TableHead>
                    <TableHead class="font-semibold text-right text-muted-foreground">
                      <Button
                        variant="ghost"
                        size="sm"
                        @click="handleSort('balance')"
                        class="h-auto p-0 font-semibold hover:bg-transparent text-muted-foreground hover:text-foreground"
                      >
                        Saldo
                        <component :is="getSortIcon('balance')" class="h-4 w-4 ml-1" :class="sortField === 'balance' ? 'text-foreground' : 'opacity-50'" />
                      </Button>
                    </TableHead>
                    <TableHead class="font-semibold text-muted-foreground">
                      <Button
                        variant="ghost"
                        size="sm"
                        @click="handleSort('role')"
                        class="h-auto p-0 font-semibold hover:bg-transparent text-muted-foreground hover:text-foreground"
                      >
                        Cargo
                        <component :is="getSortIcon('role')" class="h-4 w-4 ml-1" :class="sortField === 'role' ? 'text-foreground' : 'opacity-50'" />
                      </Button>
                    </TableHead>
                    <TableHead class="font-semibold text-muted-foreground">
                      <Button
                        variant="ghost"
                        size="sm"
                        @click="handleSort('created_at')"
                        class="h-auto p-0 font-semibold hover:bg-transparent text-muted-foreground hover:text-foreground"
                      >
                        Criado em
                        <component :is="getSortIcon('created_at')" class="h-4 w-4 ml-1" :class="sortField === 'created_at' ? 'text-foreground' : 'opacity-50'" />
                      </Button>
                    </TableHead>
                    <TableHead class="font-semibold text-muted-foreground">Ações</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow
                    v-for="user in filteredAndSortedUsers"
                    :key="user.id"
                    class="border-border hover:bg-muted/50 transition-colors"
                  >
                    <TableCell>
                      <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold text-sm">
                          {{ getInitials(user.name) }}
                        </div>
                        <div>
                          <div class="flex items-center gap-2">
                            <span class="font-medium text-foreground">{{ user.name }}</span>
                            <Badge
                              variant="outline"
                              :class="user.status === 'pending' ? 'bg-yellow-500/20 text-yellow-600 dark:text-yellow-400 border-yellow-500/50' : user.status === 'active' ? 'bg-green-500/20 text-green-600 dark:text-green-400 border-green-500/50' : 'bg-gray-500/20 text-gray-600 dark:text-gray-400 border-gray-500/50'"
                            >
                              {{ user.status === 'pending' ? 'Pendente' : user.status === 'active' ? 'Ativo' : user.status || 'Inativo' }}
                            </Badge>
                          </div>
                          <div class="text-sm text-muted-foreground">ID: {{ user.id }}</div>
                        </div>
                      </div>
                    </TableCell>
                    <TableCell class="text-foreground">{{ user.email }}</TableCell>
                    <TableCell class="text-right">
                      <span class="font-semibold" :class="parseBalance(user.balance) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                        {{ formatCurrency(user.balance) }}
                      </span>
                    </TableCell>
                    <TableCell>
                      <Badge
                        variant="outline"
                        :class="`${getRoleConfig(user.role).color} font-medium`"
                      >
                        <component :is="getRoleConfig(user.role).icon" class="h-3 w-3 mr-1" />
                        {{ getRoleConfig(user.role).label }}
                      </Badge>
                    </TableCell>
                    <TableCell class="text-muted-foreground">{{ formatDate(user.created_at) }}</TableCell>
                    <TableCell>
                      <div class="flex items-center gap-1">
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <Button variant="ghost" size="sm" class="h-8 w-8 p-0 hover:bg-accent hover:text-accent-foreground">
                              <Link :href="route('users.show', user)">
                                <Eye class="h-4 w-4" />
                              </Link>
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>
                            <p>Ver detalhes</p>
                          </TooltipContent>
                        </Tooltip>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <Button variant="ghost" size="sm" class="h-8 w-8 p-0 hover:bg-destructive/10 hover:text-destructive">
                              <Settings class="h-4 w-4" />
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>
                            <p>Configurações</p>
                          </TooltipContent>
                        </Tooltip>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <Button 
                              variant="ghost" 
                              size="sm" 
                              class="h-8 w-8 p-0 hover:bg-destructive/10 hover:text-destructive"
                              @click="confirmDelete(user)"
                            >
                              <Trash2 class="h-4 w-4" />
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>
                            <p>Excluir usuário</p>
                          </TooltipContent>
                        </Tooltip>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>

            <!-- Empty State -->
            <div
              v-if="filteredAndSortedUsers.length === 0"
              class="flex flex-col items-center justify-center py-12 text-center"
            >
              <div class="bg-muted rounded-full p-3 mb-4">
                <Users class="h-6 w-6 text-muted-foreground" />
              </div>
              <h3 class="text-lg font-semibold text-foreground mb-2">Nenhum usuário encontrado</h3>
              <p class="text-muted-foreground max-w-sm">
                {{
                  hasActiveFilters
                    ? 'Tente ajustar os filtros para encontrar os usuários desejados.'
                    : 'Quando você tiver usuários cadastrados, eles aparecerão aqui.'
                }}
              </p>
              <div class="flex gap-2 mt-4">
                <Button
                  v-if="hasActiveFilters"
                  variant="outline"
                  size="sm"
                  class="border-border hover:bg-accent hover:text-accent-foreground"
                  @click="clearFilters"
                >
                  Limpar filtros
                </Button>
                <Button
                  v-if="!hasActiveFilters"
                  size="sm"
                  class="bg-primary text-primary-foreground hover:bg-primary/90"
                >
                  <UserPlus class="h-4 w-4 mr-2" />
                  Adicionar Primeiro Usuário
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
        
        <!-- Paginação -->
        <div v-if="users && ((users.meta && users.meta.total > 10 && users.meta.last_page > 1) || (users.total > 10 && users.last_page > 1))" class="mt-4">
          <Pagination :pagination="users" />
        </div>
      </div>

      <!-- Dialog de Confirmação de Exclusão -->
      <Dialog v-model:open="showDeleteDialog">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Confirmar Exclusão</DialogTitle>
            <DialogDescription class="text-sm">
              Tem certeza que deseja excluir o usuário <strong>{{ userToDelete?.name }}</strong> ({{ userToDelete?.email }})? 
              Esta ação não pode ser desfeita.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter class="flex-col sm:flex-row gap-2">
            <Button variant="outline" @click="showDeleteDialog = false" class="w-full sm:w-auto">
              Não, Cancelar
            </Button>
            <Button variant="destructive" @click="deleteUser" class="w-full sm:w-auto">
              Sim, Excluir Usuário
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </TooltipProvider>
  </AppLayout>
</template>
