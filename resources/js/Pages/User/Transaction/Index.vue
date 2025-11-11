<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { QrCode } from 'lucide-vue-next';
import { Pagination } from '@/components/ui/pagination';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps({
    transactions: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            meta: {}
        })
    },
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
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusConfig = (status) => {
    if (!status) {
        return {
            label: 'Desconhecido',
            color: 'bg-gray-500/20 text-gray-400 border-gray-500/30'
        };
    }
    
    const statusLower = String(status).toLowerCase();
    
    // Verificar se é pago (aceitar múltiplas variações)
    if (statusLower === 'pago' || statusLower === 'paid' || statusLower === 'approved' || statusLower === 'concluida' || statusLower === 'concluída') {
        return {
            label: 'Pago',
            color: 'bg-green-500/20 text-green-400 border-green-500/30'
        };
    } 
    // Verificar se é pendente
    else if (statusLower === 'pendente' || statusLower === 'pending' || statusLower === 'waiting_payment' || statusLower === 'aguardando') {
        return {
            label: 'Pendente',
            color: 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30'
        };
    }
    // Verificar se é cancelado/expirado
    else if (statusLower === 'cancelado' || statusLower === 'cancelled' || statusLower === 'expirado' || statusLower === 'expired') {
        return {
            label: statusLower === 'expirado' || statusLower === 'expired' ? 'Expirado' : 'Cancelado',
            color: 'bg-red-500/20 text-red-400 border-red-500/30'
        };
    }
    
    return {
        label: status || 'Desconhecido',
        color: 'bg-gray-500/20 text-gray-400 border-gray-500/30'
    };
};

const getInitials = (name) => {
    if (!name) return '?';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};

const openTransaction = (transaction) => {
    // Usar transaction_id, fullpix_transaction_id, liberpay_sale_id ou id para abrir detalhes
    const id = transaction.transaction_id || transaction.fullpix_transaction_id || transaction.liberpay_sale_id || transaction.id;
    router.visit(route('transactions.show', id));
};
</script>

<template>
    <Head title="Transações" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-7xl mx-auto w-full">
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between w-full">
                <div class="flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Transações</h2>
                    <p class="text-sm sm:text-base text-muted-foreground">Últimas movimentações financeiras</p>
                </div>
            </div>

            <Card class="w-full border-border bg-card">
                <CardContent class="p-0">
                    <!-- Desktop View -->
                    <div class="hidden md:block overflow-x-auto">
                        <Table>
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
                                    v-for="transaction in (transactions?.data || [])"
                                    :key="`${transaction.id || transaction.transaction_id}-${transaction.created_at || transaction.date}`"
                                    class="border-border hover:bg-muted/50 transition-colors cursor-pointer"
                                    @click="openTransaction(transaction)"
                                >
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <Avatar class="h-8 w-8">
                                                <AvatarFallback>{{ getInitials(transaction.customer_name || transaction.customer_email || '?') }}</AvatarFallback>
                                            </Avatar>
                                            <div>
                                                <div class="font-medium text-foreground">{{ transaction.customer_name || transaction.customer_email || 'N/A' }}</div>
                                                <div class="text-xs text-muted-foreground">{{ transaction.customer_email || '' }}</div>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-green-500 rounded flex items-center justify-center">
                                                <QrCode class="h-4 w-4 text-white" />
                                            </div>
                                            <div class="font-semibold text-foreground">{{ formatCurrency(transaction.total_amount) }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline" :class="getStatusConfig(transaction.payment_status || transaction.raw_status).color">
                                            {{ getStatusConfig(transaction.payment_status || transaction.raw_status).label }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="font-medium text-foreground">{{ transaction.product || 'Depósito' }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-muted-foreground">
                                            <div v-if="transaction.paid_at" class="font-medium text-foreground">
                                                Pago em {{ formatDate(transaction.paid_at) }}
                                            </div>
                                            <div v-else>
                                                Criado em {{ formatDate(transaction.created_at || transaction.date) }}
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
                            v-for="transaction in (transactions?.data || [])"
                            :key="`${transaction.id || transaction.transaction_id}-${transaction.created_at || transaction.date}`"
                            class="border border-border rounded-lg p-4 bg-card hover:bg-muted/50 transition-colors cursor-pointer"
                            @click="openTransaction(transaction)"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <div class="w-10 h-10 bg-green-500 rounded flex items-center justify-center flex-shrink-0">
                                        <QrCode class="h-5 w-5 text-white" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-foreground truncate">{{ formatCurrency(transaction.total_amount) }}</div>
                                    </div>
                                </div>
                                <Badge variant="outline" :class="getStatusConfig(transaction.payment_status).color">
                                    {{ getStatusConfig(transaction.payment_status).label }}
                                </Badge>
                            </div>
                        </div>
                    </div>

                    <div v-if="!transactions?.data || transactions.data.length === 0" class="p-8 text-center">
                        <p class="text-muted-foreground">Nenhuma transação encontrada</p>
                    </div>
                    
                    <!-- Paginação - Dentro do CardContent, sempre mostrar quando houver mais de 10 registros -->
                    <div v-if="transactions && ((transactions.meta && transactions.meta.total > 10 && transactions.meta.last_page > 1) || (transactions.total > 10 && transactions.last_page > 1))" class="border-t border-border w-full py-2 bg-card">
                        <Pagination :pagination="transactions" />
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
