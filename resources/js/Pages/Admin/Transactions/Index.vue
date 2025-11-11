<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Search, Filter, Download, RefreshCw, CreditCard, QrCode, Receipt } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Pagination } from '@/components/ui/pagination';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Transações',
        href: '/admin/transactions',
    },
];

const props = defineProps({
    transactions: {
        type: Object,
        default: () => ({
            data: [],
            links: {},
            meta: {}
        })
    }
});

// Dados reais do backend
const transactions = computed(() => props.transactions.data || []);

const searchTerm = ref('');

const filteredTransactions = computed(() => {
    if (!searchTerm.value) return transactions.value;
    
    const term = searchTerm.value.toLowerCase();
    return transactions.value.filter(transaction => 
        (transaction.clientName?.toLowerCase().includes(term)) ||
        (transaction.clientEmail?.toLowerCase().includes(term)) ||
        (transaction.transactionId?.toLowerCase().includes(term))
    );
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
};

const getStatusConfig = (status) => {
    const statusLower = status?.toLowerCase();
    
    if (statusLower === 'concluida' || statusLower === 'pago' || statusLower === 'paid') {
        return {
            label: 'Pago',
            color: 'bg-green-500/20 text-green-400 border-green-500/30'
        };
    } else if (statusLower === 'pendente' || statusLower === 'pending') {
        return {
            label: 'Pendente',
            color: 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30'
        };
    } else if (statusLower === 'cancelada' || statusLower === 'cancelled') {
        return {
            label: 'Cancelada',
            color: 'bg-red-500/20 text-red-400 border-red-500/30'
        };
    }
    
    return {
        label: status || 'Desconhecido',
        color: 'bg-gray-500/20 text-gray-400 border-gray-500/30'
    };
};

const getMethodIcon = (method) => {
    const icons = {
        'Pix': QrCode,
        'Boleto': Receipt,
        'Cartão': CreditCard
    };
    return icons[method] || CreditCard;
};

const openTransactionDetails = (transaction) => {
    // Usar transaction_id, fullpix_transaction_id, liberpay_sale_id ou id para abrir página de detalhes
    const id = transaction.transaction_id || transaction.fullpix_transaction_id || transaction.liberpay_sale_id || transaction.id;
    window.location.href = route('admin.transactions.show', id);
};

const exportToCSV = () => {
    // Implementar exportação CSV
    console.log('Exportando para CSV...');
};
</script>

<template>
    <Head title="Transações Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mt-2 sm:mt-4 flex h-full flex-1 flex-col gap-3 sm:gap-4 p-2 sm:p-4 w-full mx-auto">
            <!-- Cabeçalho -->
            <div class="space-y-1">
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Bem-vindo na aba de Transações, Admin</h2>
                <p class="text-sm sm:text-base text-muted-foreground">
                    Nesta sessão você consegue visualizar todas as transações de todos os sellers do seu sistema!
                </p>
            </div>

            <!-- Container Principal -->
            <Card class="w-full border-border bg-card">
                <CardHeader class="pb-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <CardTitle class="text-base sm:text-lg">Transações</CardTitle>
                        <div class="flex gap-3">
                            <Button variant="outline" @click="exportToCSV" class="border-border hover:bg-accent hover:text-accent-foreground">
                                <Download class="h-4 w-4 mr-2" />
                                Exportar
                            </Button>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 mt-4">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                            <Input
                                v-model="searchTerm"
                                placeholder="Buscar transação..."
                                class="pl-10 bg-background border-border text-foreground"
                            />
                        </div>
                        <Button variant="outline" class="border-border hover:bg-accent hover:text-accent-foreground">
                            <Filter class="h-4 w-4 mr-2" />
                            Filtros
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <!-- Desktop View -->
                    <div class="hidden md:block overflow-x-auto w-full">
                        <Table class="w-full min-w-full">
                            <TableHeader>
                                <TableRow class="bg-muted/50 border-border hover:bg-muted/50">
                                    <TableHead class="font-semibold text-muted-foreground">Cliente</TableHead>
                                    <TableHead class="font-semibold text-muted-foreground">Forma de Pagamento</TableHead>
                                    <TableHead class="font-semibold text-muted-foreground">Status</TableHead>
                                    <TableHead class="font-semibold text-muted-foreground">Produto</TableHead>
                                    <TableHead class="font-semibold text-muted-foreground">Criado/Pago em</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="transaction in filteredTransactions"
                                    :key="transaction.id"
                                    class="border-border hover:bg-muted/50 transition-colors cursor-pointer"
                                    @click="openTransactionDetails(transaction)"
                                >
                                    <TableCell>
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center text-white font-semibold">
                                                {{ transaction.clientName?.charAt(0).toUpperCase() || 'U' }}
                                            </div>
                                            <div>
                                                <div class="font-medium text-foreground">{{ transaction.clientName }}</div>
                                                <div class="text-sm text-muted-foreground">{{ transaction.clientEmail }}</div>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-green-500 rounded flex items-center justify-center">
                                                <component :is="getMethodIcon(transaction.method)" class="h-4 w-4 text-white" />
                                            </div>
                                            <div>
                                                <div class="font-semibold text-foreground">{{ formatCurrency(transaction.value) }}</div>
                                                <div v-if="transaction.expires_at" class="text-xs text-muted-foreground">
                                                    Vence em {{ formatDate(transaction.expires_at) }}
                                                </div>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline" :class="getStatusConfig(transaction.status).color">
                                            {{ getStatusConfig(transaction.status).label }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div>
                                            <div class="font-medium text-foreground">{{ transaction.product || 'Depósito' }}</div>
                                            <div class="text-sm text-muted-foreground">{{ formatCurrency(transaction.value) }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-muted-foreground">
                                            <div>{{ formatDate(transaction.created_at || transaction.date) }}</div>
                                            <div v-if="transaction.paid_at" class="text-xs mt-1">
                                                Pago em {{ formatDate(transaction.paid_at) }}
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    
                    <!-- Mobile View -->
                    <div class="md:hidden space-y-3 p-4">
                        <div
                            v-for="transaction in filteredTransactions"
                            :key="transaction.id"
                            class="border border-border rounded-lg p-4 bg-card hover:bg-muted/50 transition-colors cursor-pointer"
                            @click="openTransactionDetails(transaction)"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <div class="w-10 h-10 bg-green-500 rounded flex items-center justify-center flex-shrink-0">
                                        <component :is="getMethodIcon(transaction.method)" class="h-5 w-5 text-white" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-foreground truncate">{{ formatCurrency(transaction.value) }}</div>
                                    </div>
                                </div>
                                <Badge variant="outline" :class="getStatusConfig(transaction.status).color">
                                    {{ getStatusConfig(transaction.status).label }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
            
            <!-- Paginação -->
            <div v-if="props.transactions && ((props.transactions.meta && props.transactions.meta.total > 10 && props.transactions.meta.last_page > 1) || (props.transactions.total > 10 && props.transactions.last_page > 1))" class="mt-4">
                <Pagination :pagination="props.transactions" />
            </div>

        </div>
    </AppLayout>
</template>

