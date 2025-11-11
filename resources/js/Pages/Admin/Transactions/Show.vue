<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, RotateCcw } from 'lucide-vue-next';

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
    transaction: Object,
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
    if (!dateString) return 'Não informado';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDocument = (doc) => {
    if (!doc || doc === 'Não informado') return 'Não informado';
    const cleaned = doc.replace(/\D/g, '');
    if (cleaned.length === 11) {
        return cleaned.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }
    if (cleaned.length === 14) {
        return cleaned.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    }
    return doc;
};

const formatPhone = (phone) => {
    if (!phone || phone === 'Não informado') return 'Não informado';
    const cleaned = phone.replace(/\D/g, '');
    if (cleaned.length === 11) {
        return cleaned.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    }
    if (cleaned.length === 10) {
        return cleaned.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    }
    return phone;
};

const getStatusConfig = (status) => {
    const statusLower = status?.toLowerCase();
    
    if (statusLower === 'pago' || statusLower === 'paid' || statusLower === 'concluida') {
        return {
            label: 'Pago',
            color: 'bg-green-500/20 text-green-400 border-green-500/30'
        };
    } else if (statusLower === 'pendente' || statusLower === 'pending') {
        return {
            label: 'Pendente',
            color: 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30'
        };
    }
    
    return {
        label: status || 'Desconhecido',
        color: 'bg-gray-500/20 text-gray-400 border-gray-500/30'
    };
};

const handleRefund = () => {
    if (confirm('Tem certeza que deseja estornar esta transação?')) {
        // TODO: Implementar estorno
        alert('Funcionalidade de estorno será implementada em breve');
    }
};
</script>

<template>
    <Head :title="`Transação #${transaction?.transaction_id || 'N/A'}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-7xl mx-auto w-full">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="sm" @click="router.visit(route('admin.transactions.index'))">
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Voltar
                </Button>
                <div class="flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight">
                        Detalhes da Transação #{{ transaction?.transaction_id || 'N/A' }}
                    </h2>
                    <p class="text-sm text-muted-foreground">Veja os detalhes da venda</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Coluna Principal -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Informações da Transação -->
                    <Card class="border-border bg-card">
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle>Transação</CardTitle>
                                    <CardDescription>{{ transaction?.transaction_id || 'N/A' }}</CardDescription>
                                </div>
                                <Badge variant="outline" :class="getStatusConfig(transaction?.status).color">
                                    {{ getStatusConfig(transaction?.status).label }}
                                </Badge>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-muted-foreground mb-1">Valor bruto:</p>
                                    <p class="text-lg font-semibold text-foreground">
                                        {{ formatCurrency(transaction?.total_amount || 0) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground mb-1">Taxas:</p>
                                    <p class="text-lg font-semibold text-red-600 dark:text-red-400">
                                        {{ formatCurrency(transaction?.fee || 0) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground mb-1">Valor líquido (~):</p>
                                    <p class="text-lg font-semibold text-green-600 dark:text-green-400">
                                        {{ formatCurrency(transaction?.net_deposit || 0) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground mb-1">ID da Transação</p>
                                    <p class="text-sm font-mono text-foreground">
                                        {{ transaction?.transaction_id || 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-muted-foreground mb-1">Referência Externa</p>
                                    <p class="text-sm font-mono text-foreground">
                                        {{ transaction?.external_reference || 'Não informado' }}
                                    </p>
                                </div>
                            </div>

                            <Separator />

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-muted-foreground mb-1">Criada em</p>
                                    <p class="text-sm text-foreground">{{ formatDate(transaction?.created_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground mb-1">Última Atualização</p>
                                    <p class="text-sm text-foreground">{{ formatDate(transaction?.updated_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground mb-1">Pago em</p>
                                    <p class="text-sm text-foreground">{{ formatDate(transaction?.paid_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground mb-1">Estornado em</p>
                                    <p class="text-sm text-foreground">{{ formatDate(transaction?.refunded_at) }}</p>
                                </div>
                            </div>

                            <div v-if="transaction?.status === 'pago' || transaction?.raw_status === 'paid'">
                                <Button 
                                    variant="outline" 
                                    class="w-full border-red-500/50 text-red-500 hover:bg-red-500/10"
                                    @click="handleRefund"
                                >
                                    <RotateCcw class="h-4 w-4 mr-2" />
                                    Estornar
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Informações do Cliente -->
                    <Card class="border-border bg-card">
                        <CardHeader>
                            <CardTitle>Cliente</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <p class="font-semibold text-foreground">{{ transaction?.customer?.name || 'N/A' }}</p>
                                <p class="text-sm text-muted-foreground">{{ transaction?.customer?.email || 'N/A' }}</p>
                            </div>

                            <Separator />

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-muted-foreground mb-1">CPF/CNPJ</p>
                                    <p class="text-sm text-foreground">{{ formatDocument(transaction?.customer?.document) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground mb-1">Telefone</p>
                                    <p class="text-sm text-foreground">{{ formatPhone(transaction?.customer?.phone) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground mb-1">Endereço</p>
                                    <p class="text-sm text-foreground">{{ transaction?.customer?.address || 'Não informado' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground mb-1">Bairro</p>
                                    <p class="text-sm text-foreground">{{ transaction?.customer?.neighborhood || 'Não informado' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground mb-1">Cidade/UF</p>
                                    <p class="text-sm text-foreground">{{ transaction?.customer?.city_state || 'Não informado' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground mb-1">CEP</p>
                                    <p class="text-sm text-foreground">{{ transaction?.customer?.zip_code || 'Não informado' }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

