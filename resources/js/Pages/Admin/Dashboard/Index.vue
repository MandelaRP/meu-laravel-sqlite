<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import DashboardHeader from './components/DashboardHeader.vue';
import FinancialSummary from './components/FinancialSummary.vue';
import ChartCard from './components/ChartCard.vue';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
];

const props = defineProps({
    financialData: {
        type: Object,
        default: () => ({})
    },
    chartData: {
        type: Array,
        default: () => []
    },
    paymentMethodsStatus: {
        type: Object,
        default: () => ({
            pix: true,
            credit_card: true,
            boleto: true
        })
    }
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mt-2 sm:mt-4 flex h-full flex-1 flex-col gap-3 sm:gap-4 p-2 sm:p-4 max-w-7xl w-full mx-auto">
            <!-- Cabeçalho -->
            <DashboardHeader />

            <!-- Resumo Financeiro -->
            <FinancialSummary :financial-data="financialData" />

            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                <ChartCard title="Lucro Líquido - Últimos 7 dias" :chart-data="chartData" data-key="lucro_liquido" />
                <ChartCard title="Transação Total - Últimos 7 dias" :chart-data="chartData" data-key="transacao_total" />
            </div>
        </div>
    </AppLayout>
</template>

