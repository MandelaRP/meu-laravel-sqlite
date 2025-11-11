<script setup>
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Calendar, Filter } from 'lucide-vue-next';
import {
    DollarSign,
    TrendingUp,
    ArrowDownCircle,
    Users,
    Wallet,
    CreditCard,
    BarChart3,
    PieChart
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};

// Função para obter a data do dia anterior formatada
const getDiaAnterior = () => {
    const hoje = new Date();
    const diaAnterior = new Date(hoje);
    diaAnterior.setDate(hoje.getDate() - 1);
    
    const dia = String(diaAnterior.getDate()).padStart(2, '0');
    const mes = String(diaAnterior.getMonth() + 1).padStart(2, '0');
    const ano = diaAnterior.getFullYear();
    
    return `${dia}/${mes}/${ano}`;
};

const diaAnteriorFormatado = computed(() => getDiaAnterior());

const props = defineProps({
    financialData: {
        type: Object,
        default: () => ({
            lucroLiquidoHoje: 0,
            transacaoTotalHoje: 0,
            saquesHoje: 0,
            totalUsuarios: 0,
            lucroLiquidoDiaAnterior: 0,
            transacaoTotalDiaAnterior: 0,
            saquesDiaAnterior: 0,
            valorTransacionadoTotal: 0,
            saldoDisponivel: 0,
            faturamento: 0,
            quantidadeVendas: 0,
            ticketMedio: 0,
            paymentMethods: {
                pix: 0,
                credit_card: 0,
                boleto: 0
            },
            liberpayFee: 0.01
        })
    }
});

const financialData = computed(() => props.financialData);

const cards = computed(() => [
    {
        title: 'Lucro Líquido (Hoje)',
        value: financialData.value.lucroLiquidoHoje || 0,
        icon: DollarSign,
        color: 'bg-gray-500'
    },
    {
        title: 'Transação Total (Hoje)',
        value: financialData.value.transacaoTotalHoje || 0,
        icon: TrendingUp,
        color: 'bg-gray-500'
    },
    {
        title: 'Saques (Hoje)',
        value: financialData.value.saquesHoje || 0,
        icon: ArrowDownCircle,
        color: 'bg-gray-500'
    },
    {
        title: 'Total de Usuários',
        value: financialData.value.totalUsuarios || 0,
        icon: Users,
        color: 'bg-gray-500',
        isNumber: true
    },
    {
        title: `Lucro Líquido (${diaAnteriorFormatado.value})`,
        value: financialData.value.lucroLiquidoDiaAnterior || 0,
        icon: Wallet,
        color: 'bg-gray-500'
    },
    {
        title: `Transação Total (${diaAnteriorFormatado.value})`,
        value: financialData.value.transacaoTotalDiaAnterior || 0,
        icon: CreditCard,
        color: 'bg-gray-500'
    },
    {
        title: `Saques (${diaAnteriorFormatado.value})`,
        value: financialData.value.saquesDiaAnterior || 0,
        icon: BarChart3,
        color: 'bg-gray-500'
    },
    {
        title: 'Valor Transacionado Total',
        value: financialData.value.valorTransacionadoTotal || 0,
        icon: PieChart,
        color: 'bg-gray-500'
    }
]);
</script>

<template>
    <Card class="w-full border-border bg-card">
        <CardHeader class="pb-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <CardTitle class="text-base sm:text-lg">Resumo Financeiro</CardTitle>
                    <CardDescription class="text-sm mt-1">
                        Visão geral dos dados financeiros da plataforma.
                    </CardDescription>
                </div>
                <Button variant="outline" class="border-border hover:bg-accent hover:text-accent-foreground">
                    <Filter class="h-4 w-4 mr-2" />
                    <Calendar class="h-4 w-4 mr-2" />
                    Selecionar data
                </Button>
            </div>
        </CardHeader>
        <CardContent>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div
                    v-for="(card, index) in cards"
                    :key="index"
                    class="flex gap-3 sm:gap-4 rounded-xl border bg-card p-4 sm:p-6"
                >
                    <div :class="['flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-lg', card.color]">
                        <component :is="card.icon" class="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs sm:text-sm text-muted-foreground">{{ card.title }}</span>
                        <span class="text-lg sm:text-xl font-semibold text-card-foreground">
                            {{ card.isNumber ? card.value.toLocaleString('pt-BR') : (card.isFee ? formatCurrency(card.value) : formatCurrency(card.value)) }}
                        </span>
                        <span v-if="card.description" class="text-xs text-muted-foreground mt-1">
                            {{ card.description }}
                        </span>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

