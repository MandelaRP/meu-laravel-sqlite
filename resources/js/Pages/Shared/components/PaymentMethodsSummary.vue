<script setup>
import { ref, computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
  CreditCard,
  Smartphone,
  FileText,
  TrendingUp,
  RefreshCw,
  CalendarClock,
  AlertCircle,
} from 'lucide-vue-next'

const props = defineProps({
  transactions: {
    type: Array,
    default: () => [],
  },
  paymentMethods: {
    type: Object,
    default: () => ({
      pix: 0,
      card: 0,
      boleto: 0,
    }),
  },
  financialSummary: {
    type: Object,
    default: () => ({
      conversao: 0,
      reembolsos: 0,
      preChargeback: 0,
      chargeback: 0,
    }),
  },
})

// Cor padrão dos ícones e tracinhos (verde LuckPay - configurável via CSS)
const iconColor = 'text-green-500'
const accentColor = 'bg-green-500' // Cor do tracinho e barras de progresso (configurável)

// Dados de exemplo se não houver transações
const defaultTransactions = [
  { paymentMethod: 'PIX', status: 'paid' },
  { paymentMethod: 'PIX', status: 'paid' },
  { paymentMethod: 'PIX', status: 'paid' },
  { paymentMethod: 'Cartão de Crédito', status: 'paid' },
  { paymentMethod: 'Cartão de Crédito', status: 'paid' },
  { paymentMethod: 'Boleto', status: 'paid' },
]

// Usar dados reais do backend ou calcular a partir das transações
const paymentMethodsData = computed(() => {
  // Se paymentMethods foi passado diretamente, usar
  if (props.paymentMethods && (props.paymentMethods.pix > 0 || props.paymentMethods.card > 0 || props.paymentMethods.boleto > 0)) {
    return [
      {
        icon: Smartphone,
        name: 'Pix',
        percentage: props.paymentMethods.pix || 0,
      },
      {
        icon: CreditCard,
        name: 'Cartão de Crédito',
        percentage: props.paymentMethods.card || 0,
      },
      {
        icon: FileText,
        name: 'Boleto',
        percentage: props.paymentMethods.boleto || 0,
      },
    ];
  }

  // Fallback: calcular a partir das transações
  const transactionsData = props.transactions && props.transactions.length > 0 
    ? props.transactions 
    : []

  const methods = {
    'PIX': { count: 0, icon: Smartphone, name: 'Pix' },
    'Cartão de Crédito': { count: 0, icon: CreditCard, name: 'Cartão de Crédito' },
    'Boleto': { count: 0, icon: FileText, name: 'Boleto' },
  }

  transactionsData.forEach(transaction => {
    const method = transaction.paymentMethod || transaction.method || transaction.metodo
    if (method && methods[method]) {
      methods[method].count++
    } else if (method && (method.toLowerCase().includes('pix') || method.toLowerCase() === 'pix')) {
      methods['PIX'].count++
    } else if (method && (method.toLowerCase().includes('cartão') || method.toLowerCase().includes('card'))) {
      methods['Cartão de Crédito'].count++
    } else if (method && method.toLowerCase().includes('boleto')) {
      methods['Boleto'].count++
    }
  })

  const total = Object.values(methods).reduce((sum, m) => sum + m.count, 0) || 1

  return Object.values(methods).map(method => ({
    ...method,
    percentage: total > 0 ? Math.round((method.count / total) * 100) : 0,
  }))
})

// Usar dados reais do backend ou calcular a partir das transações
const financialSummaryData = computed(() => {
  // Se financialSummary foi passado diretamente, usar
  if (props.financialSummary && (props.financialSummary.conversao > 0 || props.financialSummary.reembolsos > 0 || props.financialSummary.conversion > 0 || props.financialSummary.refunds > 0)) {
    return {
      conversion: props.financialSummary.conversao || props.financialSummary.conversion || 0,
      refunds: props.financialSummary.reembolsos || props.financialSummary.refunds || 0,
      preChargeback: props.financialSummary.preChargeback || 0,
      chargeback: props.financialSummary.chargeback || 0,
    };
  }

  // Fallback: calcular a partir das transações
  const transactionsData = props.transactions && props.transactions.length > 0 
    ? props.transactions 
    : []

  const totalTransactions = transactionsData.length || 1
  const paidTransactions = transactionsData.filter(
    t => (t.paymentStatus || t.status || t.tipo || '').toLowerCase() === 'paid' || 
         (t.paymentStatus || t.status || t.tipo || '').toLowerCase() === 'entrada'
  ).length

  const refundedCount = transactionsData.filter(
    t => (t.paymentStatus || t.status || '').toLowerCase() === 'refunded' || 
         (t.paymentStatus || t.status || '').toLowerCase() === 'unpaid'
  ).length

  // Usar conversão do backend (já calculada corretamente baseada em PIX gerados vs pagos)
  // Se não vier do backend, calcular como fallback
  const conversionRate = props.financialSummary?.conversao !== undefined 
    ? parseFloat(props.financialSummary.conversao.toFixed(2))
    : (totalTransactions > 0 ? Math.round((paidTransactions / totalTransactions) * 100) : 0)

  return {
    conversion: conversionRate,
    refunds: refundedCount,
    preChargeback: props.financialSummary?.preChargeback || 0,
    chargeback: props.financialSummary?.chargeback || 0,
  }
})

// Formatar conversão com 2 casas decimais
const formatConversion = (value) => {
  if (value === null || value === undefined || isNaN(value)) return '0%';
  return parseFloat(value.toFixed(2)) + '%';
}
</script>

<template>
  <Card class="w-full h-full border-border bg-card flex flex-col">
    <CardHeader class="pb-3 sm:pb-4">
      <div class="flex items-center gap-2">
        <div :class="['h-5 w-1 rounded-full flex-shrink-0', accentColor]"></div>
        <CardTitle class="text-base sm:text-lg">Métodos de Pagamento</CardTitle>
      </div>
    </CardHeader>
    <CardContent class="px-3 sm:px-6 flex-1 flex flex-col pb-3 sm:pb-4">
      <!-- Seção 1: Métodos de Pagamento -->
      <div class="mb-6">
        <div class="space-y-5">
          <div
            v-for="method in paymentMethodsData"
            :key="method.name"
            class="space-y-2"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3 flex-1 min-w-0">
                <component
                  :is="method.icon"
                  :class="['h-5 w-5 flex-shrink-0', iconColor]"
                />
                <span class="font-medium text-foreground text-sm sm:text-base">
                  {{ method.name }}
                </span>
              </div>
              <span :class="['font-semibold text-sm sm:text-base', iconColor]">
                {{ method.percentage }}%
              </span>
            </div>
            <div class="w-full bg-muted rounded-full h-1 overflow-hidden">
              <div
                :class="['h-full rounded-full transition-all duration-500', accentColor]"
                :style="{ width: `${method.percentage}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Divisória -->
      <div class="border-t border-border my-4"></div>

      <!-- Seção 2: Resumo Financeiro -->
      <div class="flex-1 flex flex-col">
        <div class="flex items-center gap-2 mb-3">
          <div :class="['h-5 w-1 rounded-full flex-shrink-0', accentColor]"></div>
          <CardTitle class="text-sm sm:text-base font-semibold text-card-foreground">
            Resumo Financeiro
          </CardTitle>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <!-- Conversão -->
          <div class="flex items-center gap-2.5 p-2 rounded-lg bg-card border border-border">
            <div
              :class="[
                'flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 flex-shrink-0',
              ]"
            >
              <TrendingUp :class="['h-4 w-4', iconColor]" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-[10px] sm:text-xs text-muted-foreground mb-0.5">Conversão</p>
              <p :class="['text-sm sm:text-base font-bold', iconColor]">
                {{ formatConversion(financialSummaryData.conversion) }}
              </p>
            </div>
          </div>

          <!-- Qtde. Reembolsos -->
          <div class="flex items-center gap-2.5 p-2 rounded-lg bg-card border border-border">
            <div
              :class="[
                'flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 flex-shrink-0',
              ]"
            >
              <RefreshCw :class="['h-4 w-4', iconColor]" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-[10px] sm:text-xs text-muted-foreground mb-0.5">Qtde. Reembolsos</p>
              <p :class="['text-sm sm:text-base font-bold', iconColor]">
                {{ financialSummaryData.refunds }}
              </p>
            </div>
          </div>

          <!-- Pré-Chargeback -->
          <div class="flex items-center gap-2.5 p-2 rounded-lg bg-card border border-border">
            <div
              :class="[
                'flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 flex-shrink-0',
              ]"
            >
              <CalendarClock :class="['h-4 w-4', iconColor]" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-[10px] sm:text-xs text-muted-foreground mb-0.5">Pré-Chargeback</p>
              <p :class="['text-sm sm:text-base font-bold', iconColor]">
                {{ financialSummaryData.preChargeback }}%
              </p>
            </div>
          </div>

          <!-- Chargeback -->
          <div class="flex items-center gap-2.5 p-2 rounded-lg bg-card border border-border">
            <div
              :class="[
                'flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 flex-shrink-0',
              ]"
            >
              <AlertCircle :class="['h-4 w-4', iconColor]" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-[10px] sm:text-xs text-muted-foreground mb-0.5">Chargeback</p>
              <p :class="['text-sm sm:text-base font-bold', iconColor]">
                {{ financialSummaryData.chargeback }}%
              </p>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

