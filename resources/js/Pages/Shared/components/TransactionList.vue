<script setup>
import { ref, computed } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
  Search,
  Filter,
  ArrowUpDown,
  ArrowUp,
  ArrowDown,
  CreditCard,
  Smartphone,
  FileText,
  Building2,
  DollarSign,
  ChevronDown,
} from 'lucide-vue-next'

const props = defineProps({
  title: {
    type: String,
    default: 'Transações Recentes',
  },
  description: {
    type: String,
    default: 'Últimas movimentações financeiras',
  },
  transactions: {
    type: Array,
    default: () => [],
  },
})

// Cores dos status (configuráveis via CSS)
const statusColors = {
  paid: {
    bg: 'bg-[#00C48C]/10',
    text: 'text-[#00C48C]',
    border: 'border-[#00C48C]/20',
    label: 'Pago'
  },
  pending: {
    bg: 'bg-[#FFC107]/10',
    text: 'text-[#FFC107]',
    border: 'border-[#FFC107]/20',
    label: 'Pendente'
  },
  cancelled: {
    bg: 'bg-[#F44336]/10',
    text: 'text-[#F44336]',
    border: 'border-[#F44336]/20',
    label: 'Cancelado'
  },
  unpaid: {
    bg: 'bg-[#F44336]/10',
    text: 'text-[#F44336]',
    border: 'border-[#F44336]/20',
    label: 'Cancelado'
  },
  refunded: {
    bg: 'bg-[#F44336]/10',
    text: 'text-[#F44336]',
    border: 'border-[#F44336]/20',
    label: 'Reembolsado'
  }
}

// Estado reativo
const searchTerm = ref('')
const statusFilter = ref('all')
const sortField = ref(null)
const sortDirection = ref('desc')

// Dados das transações (usando props - dados reais do backend)
const transactionsData = computed(() => {
  if (props.transactions && props.transactions.length > 0) {
    return props.transactions.map(t => ({
      invoice: t.invoice || t.id,
      paymentStatus: t.payment_status || t.raw_status || 'Pending',
      totalAmount: t.total_amount || 0,
      paymentMethod: t.payment_method || 'N/A',
      fee: t.fee || 0,
      product: t.product || 'Produto Digital',
      date: t.date || t.created_at || new Date().toISOString(),
    }))
  }
  return []
})

// Funções utilitárias
const getStatusConfig = (status) => {
  const statusLower = status?.toLowerCase()
  
  // Mapear status em português e inglês
  if (statusLower === 'paid' || statusLower === 'pago') {
    return statusColors.paid
  } else if (statusLower === 'pending' || statusLower === 'pendente') {
    return statusColors.pending
  } else if (statusLower === 'cancelled' || statusLower === 'unpaid' || statusLower === 'cancelado') {
    return statusColors.cancelled
  } else if (statusLower === 'refunded' || statusLower === 'reembolsado') {
    return statusColors.refunded
  }
  
  return {
    bg: 'bg-muted/50',
    text: 'text-muted-foreground',
    border: 'border-border',
    label: status || 'Desconhecido'
  }
}

// Labels dos status para o dropdown
const getStatusLabel = (status) => {
  switch (status?.toLowerCase()) {
    case 'paid':
      return 'Pago'
    case 'pending':
      return 'Pendente'
    case 'refunded':
      return 'Reembolsado'
    case 'cancelled':
    case 'unpaid':
      return 'Cancelado'
    default:
      return 'Todos'
  }
}

const statusLabel = computed(() => {
  if (statusFilter.value === 'all') {
    return 'Todos os status'
  }
  return getStatusLabel(statusFilter.value)
})

const getPaymentMethodIcon = (method) => {
  switch (method?.toLowerCase()) {
    case 'pix':
      return Smartphone
    case 'ted':
      return Building2
    case 'boleto':
      return FileText
    case 'cartão':
    case 'card':
      return CreditCard
    default:
      return DollarSign
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}

const formatCurrency = (value) => {
  if (typeof value === 'string') {
    // Se já está formatado, retornar como está
    if (value.includes('R$')) {
      return value
    }
    // Converter string para número
    value = parseFloat(value.replace(/[^\d,.-]/g, '').replace(',', '.'))
  }
  // Garantir que value é um número
  const numValue = typeof value === 'number' ? value : parseFloat(value) || 0
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(numValue)
}

const parseAmount = (amount) => {
  return parseFloat(amount.replace(/[R$\s.]/g, '').replace(',', '.'))
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
const filteredAndSortedTransactions = computed(() => {
  const filtered = transactionsData.value.filter((transaction) => {
    const matchesSearch =
      transaction.invoice?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      transaction.acquirerRef?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      transaction.id?.toString().includes(searchTerm.value.toLowerCase())
    
    const matchesStatus = statusFilter.value === 'all' || 
      transaction.paymentStatus?.toLowerCase() === statusFilter.value ||
      transaction.status?.toLowerCase() === statusFilter.value ||
      transaction.raw_status?.toLowerCase() === statusFilter.value ||
      // Mapear status em português
      (statusFilter.value === 'paid' && (transaction.paymentStatus?.toLowerCase() === 'pago')) ||
      (statusFilter.value === 'pending' && (transaction.paymentStatus?.toLowerCase() === 'pendente')) ||
      (statusFilter.value === 'refunded' && (transaction.paymentStatus?.toLowerCase() === 'reembolsado'))

    return matchesSearch && matchesStatus
  })

  if (sortField.value) {
    filtered.sort((a, b) => {
      let aValue = a[sortField.value]
      let bValue = b[sortField.value]

      if (sortField.value === 'date') {
        aValue = new Date(aValue).getTime().toString()
        bValue = new Date(bValue).getTime().toString()
      }

      if (sortField.value === 'totalAmount' || sortField.value === 'fee') {
        // Se já é número, usar diretamente
        aValue = typeof aValue === 'number' ? aValue : parseAmount(String(aValue || '0'))
        bValue = typeof bValue === 'number' ? bValue : parseAmount(String(bValue || '0'))
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
</script>

<template>
  <Card class="w-full border-border bg-card">
    <CardHeader class="pb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <CardTitle class="text-2xl font-bold text-card-foreground">{{ title }}</CardTitle>
          <CardDescription class="text-base mt-1 text-muted-foreground">{{ description }}</CardDescription>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-col sm:flex-row gap-3 mt-6">
        <div class="relative flex-1">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
          <Input
            v-model="searchTerm"
            placeholder="Buscar por ID..."
            class="pl-10 bg-background border-border text-foreground placeholder:text-muted-foreground"
          />
        </div>
        <Select v-model="statusFilter">
          <SelectTrigger class="w-full sm:w-auto sm:min-w-[180px] bg-background border-border text-foreground">
            <Filter class="h-4 w-4 mr-2 text-muted-foreground flex-shrink-0" />
            <SelectValue>
              <template v-if="statusFilter === 'all'">Todos os status</template>
              <template v-else>{{ statusLabel }}</template>
            </SelectValue>
          </SelectTrigger>
          <SelectContent class="bg-popover border-border">
            <SelectItem value="all" class="text-popover-foreground hover:bg-accent hover:text-accent-foreground">Todos</SelectItem>
            <SelectItem value="pending" class="text-popover-foreground hover:bg-accent hover:text-accent-foreground">Pendente</SelectItem>
            <SelectItem value="paid" class="text-popover-foreground hover:bg-accent hover:text-accent-foreground">Pago</SelectItem>
            <SelectItem value="refunded" class="text-popover-foreground hover:bg-accent hover:text-accent-foreground">Reembolsado</SelectItem>
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
                  @click="handleSort('invoice')"
                  class="h-auto p-0 font-semibold hover:bg-transparent text-muted-foreground hover:text-foreground"
                >
                  ID
                  <component :is="getSortIcon('invoice')" class="h-4 w-4 ml-1" :class="sortField === 'invoice' ? 'text-foreground' : 'opacity-50'" />
                </Button>
              </TableHead>
              <TableHead class="font-semibold text-muted-foreground">
                <Button
                  variant="ghost"
                  size="sm"
                  @click="handleSort('paymentMethod')"
                  class="h-auto p-0 font-semibold hover:bg-transparent text-muted-foreground hover:text-foreground"
                >
                  Método
                  <component :is="getSortIcon('paymentMethod')" class="h-4 w-4 ml-1" :class="sortField === 'paymentMethod' ? 'text-foreground' : 'opacity-50'" />
                </Button>
              </TableHead>
              <TableHead class="font-semibold text-muted-foreground">
                <Button
                  variant="ghost"
                  size="sm"
                  @click="handleSort('paymentStatus')"
                  class="h-auto p-0 font-semibold hover:bg-transparent text-muted-foreground hover:text-foreground"
                >
                  Status
                  <component :is="getSortIcon('paymentStatus')" class="h-4 w-4 ml-1" :class="sortField === 'paymentStatus' ? 'text-foreground' : 'opacity-50'" />
                </Button>
              </TableHead>
              <TableHead class="font-semibold text-right text-muted-foreground">
                <Button
                  variant="ghost"
                  size="sm"
                  @click="handleSort('totalAmount')"
                  class="h-auto p-0 font-semibold hover:bg-transparent text-muted-foreground hover:text-foreground"
                >
                  Valor
                  <component :is="getSortIcon('totalAmount')" class="h-4 w-4 ml-1" :class="sortField === 'totalAmount' ? 'text-foreground' : 'opacity-50'" />
                </Button>
              </TableHead>
              <TableHead class="font-semibold text-right text-muted-foreground">
                <Button
                  variant="ghost"
                  size="sm"
                  @click="handleSort('fee')"
                  class="h-auto p-0 font-semibold hover:bg-transparent text-muted-foreground hover:text-foreground"
                >
                  Taxa
                  <component :is="getSortIcon('fee')" class="h-4 w-4 ml-1" :class="sortField === 'fee' ? 'text-foreground' : 'opacity-50'" />
                </Button>
              </TableHead>
              <TableHead class="font-semibold text-muted-foreground">
                <Button
                  variant="ghost"
                  size="sm"
                  @click="handleSort('product')"
                  class="h-auto p-0 font-semibold hover:bg-transparent text-muted-foreground hover:text-foreground"
                >
                  Produto
                  <component :is="getSortIcon('product')" class="h-4 w-4 ml-1" :class="sortField === 'product' ? 'text-foreground' : 'opacity-50'" />
                </Button>
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="transaction in filteredAndSortedTransactions"
              :key="transaction.invoice || transaction.id"
              class="border-border hover:bg-muted/50 transition-colors"
            >
              <TableCell class="font-mono font-medium text-foreground">{{ transaction.invoice || transaction.id || '-' }}</TableCell>
              <TableCell>
                <div class="flex items-center gap-2">
                  <component :is="getPaymentMethodIcon(transaction.paymentMethod || transaction.method)" class="h-4 w-4 text-muted-foreground" />
                  <span class="font-medium text-foreground">{{ transaction.paymentMethod || transaction.method || '-' }}</span>
                </div>
              </TableCell>
              <TableCell>
                <Badge
                  variant="outline"
                  :class="`${getStatusConfig(transaction.paymentStatus || transaction.status).bg} ${getStatusConfig(transaction.paymentStatus || transaction.status).text} ${getStatusConfig(transaction.paymentStatus || transaction.status).border} font-bold border`"
                >
                  {{ getStatusConfig(transaction.paymentStatus || transaction.status).label }}
                </Badge>
              </TableCell>
              <TableCell class="text-right font-semibold text-foreground">
                {{ formatCurrency(transaction.totalAmount || transaction.amount || transaction.value || 'R$ 0,00') }}
              </TableCell>
              <TableCell class="text-right font-medium text-red-600 dark:text-red-400">
                {{ formatCurrency(transaction.fee || transaction.tax || 'R$ 0,00') }}
              </TableCell>
              <TableCell class="font-medium text-foreground">{{ transaction.product || transaction.product_name || '-' }}</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Empty State -->
      <div
        v-if="filteredAndSortedTransactions.length === 0"
        class="flex flex-col items-center justify-center py-12 text-center"
      >
        <div class="bg-muted rounded-full p-3 mb-4">
          <FileText class="h-6 w-6 text-muted-foreground" />
        </div>
        <h3 class="text-lg font-semibold text-foreground mb-2">Nenhuma transação encontrada</h3>
        <p class="text-muted-foreground max-w-sm">
          {{
            hasActiveFilters
              ? 'Tente ajustar os filtros para encontrar as transações desejadas.'
              : 'Quando você tiver transações, elas aparecerão aqui.'
          }}
        </p>
        <Button
          v-if="hasActiveFilters"
          variant="outline"
          size="sm"
          class="mt-4 border-border hover:bg-accent hover:text-accent-foreground"
          @click="clearFilters"
        >
          Limpar filtros
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
