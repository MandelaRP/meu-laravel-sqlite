<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { CircleDollarSign, Wallet, Clock, Shield, ArrowDownCircle, ArrowUpCircle, Handshake, DollarSign } from 'lucide-vue-next';
import { ref } from 'vue';
import NewSale from '@/pages/Sales/NewSale.vue';
import NewWithdrawal from '@/pages/Withdrawal/NewWithdrawal.vue';
import { Pagination } from '@/components/ui/pagination';

// Cor padrão dos ícones (verde LuckPay)
const iconColor = 'text-green-500';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];


const props = defineProps({
    extratoData: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            meta: {}
        })
    },
    saldoDisponivel: {
        type: Number,
        default: 0
    },
    saldoPendente: {
        type: Number,
        default: 0
    },
    aguardandoAntecipacao: {
        type: Number,
        default: 0
    },
    reservaFinanceira: {
        type: Number,
        default: 0
    },
    acquirerFixedFee: {
        type: Number,
        default: 1.00
    },
    acquirerPercentageFee: {
        type: Number,
        default: 0
    },
    gatewayPixFixed: {
        type: Number,
        default: 0.04
    },
    gatewayPixPercentage: {
        type: Number,
        default: 0
    },
    minWithdraw: {
        type: Number,
        default: 10.00
    },
    fixedWithdrawFee: {
        type: Number,
        default: 5.00
    },
    percentWithdrawFee: {
        type: Number,
        default: 0
    },
    acquirerWithdrawalFee: {
        type: Number,
        default: 0.50
    },
    pixKeys: {
        type: Array,
        default: () => []
    },
    withdrawals: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            meta: {}
        })
    }
});

const formatCurrency = (value) => {
    if (value === null || value === undefined) return 'R$ 0,00';
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
};

// Formatar chave PIX
const formatPixKey = (withdrawal) => {
    if (!withdrawal || !withdrawal.pix_key) return 'N/A';
    
    const key = withdrawal.pix_key;
    const type = withdrawal.pix_type;
    
    switch (type) {
        case 'CPF':
            const cpf = key.replace(/\D/g, '');
            return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        case 'CNPJ':
            const cnpj = key.replace(/\D/g, '');
            return cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
        case 'PHONE':
            const phone = key.replace(/\D/g, '');
            if (phone.length === 11) {
                return phone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            }
            return phone;
        default:
            return key;
    }
};

// Obter label do status
const getStatusLabel = (status) => {
    return status || 'Pendente';
};

// Obter variant do Badge baseado no status
const getStatusVariant = (status) => {
    switch (status) {
        case 'Aprovado':
        case 'Finalizado':
            return 'default'; // Usar classe customizada para verde
        case 'Processando':
            return 'secondary';
        case 'Cancelado':
        case 'Falhou':
        case 'Recusado':
            return 'destructive';
        default:
            return 'outline';
    }
};

// Obter cor do Badge baseado no status
const getStatusColor = (status) => {
    switch (status) {
        case 'Aprovado':
        case 'Finalizado':
            return 'bg-green-500/20 text-green-400 border-green-500/30';
        case 'Processando':
            return 'bg-blue-500/20 text-blue-400 border-blue-500/30';
        case 'Cancelado':
        case 'Falhou':
        case 'Recusado':
            return 'bg-red-500/20 text-red-400 border-red-500/30';
        default:
            return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
    }
};

// Formatar data
const formatDate = (dateString) => {
    if (!dateString) return 'Não informado';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

</script>

<template>
    <Head title="Financeiro" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-7xl mx-auto w-full">
            <!-- Título e Descrição -->
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between w-full">
                <div class="flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Financeiro</h2>
                    <p class="text-sm sm:text-base text-muted-foreground">Acompanhe seus saldos, depósitos, saques e antecipações.</p>
                </div>
            </div>

            <!-- Cards Superiores -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- Saldo Disponível -->
                <Card>
                    <CardContent class="p-3 sm:p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-xs sm:text-sm text-muted-foreground mb-1">Saldo disponível</p>
                                <p class="text-xl sm:text-2xl font-bold">{{ formatCurrency(saldoDisponivel) }}</p>
                                <div class="flex items-center justify-start gap-2 mt-3 text-xs">
                                    <NewWithdrawal
                                        :available-balance="saldoDisponivel"
                                        :min-withdraw="minWithdraw"
                                        :fixed-withdraw-fee="fixedWithdrawFee"
                                        :percent-withdraw-fee="percentWithdrawFee"
                                        :acquirer-withdrawal-fee="acquirerWithdrawalFee"
                                    >
                                        <template #trigger>
                                            <button class="text-green-500 hover:underline hover:text-green-400 transition-all cursor-pointer flex items-center gap-1.5">
                                                <ArrowDownCircle class="h-4 w-4 flex-shrink-0" />
                                                <span class="whitespace-nowrap text-sm">Solicitar saque</span>
                                            </button>
                                        </template>
                                    </NewWithdrawal>
                                    <span class="text-muted-foreground">ou</span>
                                    <NewSale
                                        :acquirer-fixed-fee="acquirerFixedFee"
                                        :acquirer-percentage-fee="acquirerPercentageFee"
                                        :gateway-pix-fixed="gatewayPixFixed"
                                        :gateway-pix-percentage="gatewayPixPercentage"
                                    >
                                        <template #trigger>
                                            <button
                                                class="text-green-500 hover:underline hover:text-green-400 transition-all cursor-pointer flex items-center gap-1.5"
                                            >
                                                <ArrowUpCircle class="h-4 w-4 flex-shrink-0" />
                                                <span class="whitespace-nowrap text-sm">Depositar</span>
                                            </button>
                                        </template>
                                    </NewSale>
                                </div>
                            </div>
                            <div class="flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30 flex-shrink-0">
                                <CircleDollarSign :class="['h-4 w-4 sm:h-5 sm:w-5', iconColor]" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Saldo Pendente -->
                <Card>
                    <CardContent class="p-3 sm:p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-xs sm:text-sm text-muted-foreground mb-1">Saldo pendente</p>
                                <p class="text-xl sm:text-2xl font-bold">{{ formatCurrency(saldoPendente) }}</p>
                                <div class="flex items-center justify-start gap-2 mt-3 text-xs">
                                    <button class="text-green-500 hover:underline hover:text-green-400 transition-all cursor-pointer flex items-center gap-1.5">
                                        <Handshake class="h-4 w-4 flex-shrink-0" />
                                        <span class="whitespace-nowrap text-sm">Solicitar antecipação</span>
                                    </button>
                                </div>
                            </div>
                            <div class="flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30 flex-shrink-0">
                                <Clock :class="['h-4 w-4 sm:h-5 sm:w-5', iconColor]" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Aguardando Antecipação -->
                <Card>
                    <CardContent class="p-3 sm:p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-xs sm:text-sm text-muted-foreground mb-1">Aguardando antecipação</p>
                                <p class="text-xl sm:text-2xl font-bold">{{ formatCurrency(aguardandoAntecipacao) }}</p>
                            </div>
                            <div class="flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30 flex-shrink-0">
                                <ArrowDownCircle :class="['h-4 w-4 sm:h-5 sm:w-5', iconColor]" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Reserva Financeira -->
                <Card>
                    <CardContent class="p-3 sm:p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-xs sm:text-sm text-muted-foreground mb-1">Reserva financeira</p>
                                <p class="text-xl sm:text-2xl font-bold">{{ formatCurrency(reservaFinanceira) }}</p>
                            </div>
                            <div class="flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30 flex-shrink-0">
                                <Shield :class="['h-4 w-4 sm:h-5 sm:w-5', iconColor]" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Abas -->
            <Card>
                <CardContent class="p-0">
                    <Tabs default-value="extrato" class="w-full">
                        <div class="border-b px-4 sm:px-6 pt-4">
                            <TabsList class="grid w-full grid-cols-3">
                                <TabsTrigger value="extrato" class="data-[state=active]:bg-primary data-[state=active]:text-primary-foreground">
                                    Extrato
                                </TabsTrigger>
                                <TabsTrigger value="transferencias" class="data-[state=active]:bg-primary data-[state=active]:text-primary-foreground">
                                    Transferências
                                </TabsTrigger>
                                <TabsTrigger value="antecipacoes" class="data-[state=active]:bg-primary data-[state=active]:text-primary-foreground">
                                    Antecipações
                                </TabsTrigger>
                            </TabsList>
                        </div>

                        <!-- Conteúdo da aba Extrato -->
                        <TabsContent value="extrato" class="p-4 sm:p-6 m-0">
                            <div class="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Descrição</TableHead>
                                            <TableHead>Tipo de Movimentação</TableHead>
                                            <TableHead class="text-right">Entrada</TableHead>
                                            <TableHead class="text-right">Saída</TableHead>
                                            <TableHead>Data</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="item in (extratoData.data || [])" :key="item.id">
                                            <TableCell class="font-medium">{{ item.descricao }}</TableCell>
                                            <TableCell>{{ item.tipo }}</TableCell>
                                            <TableCell class="text-right">
                                                <span v-if="item.entrada" class="text-green-600 dark:text-green-400 font-medium">
                                                    {{ formatCurrency(item.entrada) }}
                                                </span>
                                                <span v-else class="text-muted-foreground">R$ 0,00</span>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <span v-if="item.saida" class="text-red-600 dark:text-red-400 font-medium">
                                                    {{ formatCurrency(item.saida) }}
                                                </span>
                                                <span v-else class="text-muted-foreground">R$ 0,00</span>
                                            </TableCell>
                                            <TableCell class="text-muted-foreground">{{ item.data }}</TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                        </TabsContent>
                        
                        <!-- Paginação para Extrato -->
                        <div v-if="extratoData && ((extratoData.meta && extratoData.meta.total > 10 && extratoData.meta.last_page > 1) || (extratoData.total > 10 && extratoData.last_page > 1))" class="border-t border-border px-4 sm:px-6 py-2 bg-card">
                            <Pagination :pagination="extratoData" />
                        </div>

                        <!-- Conteúdo da aba Transferências -->
                        <TabsContent value="transferencias" class="p-4 sm:p-6 m-0">
                            <div class="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead class="hidden sm:table-cell">Valor Total</TableHead>
                                            <TableHead>Destino</TableHead>
                                            <TableHead class="hidden sm:table-cell">Status</TableHead>
                                            <TableHead class="hidden md:table-cell">Data da Solicitação</TableHead>
                                            <TableHead class="hidden lg:table-cell">Data do Pagamento</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-if="!withdrawals?.data || withdrawals.data.length === 0">
                                            <TableCell colspan="5" class="text-center py-8">
                                                <p class="text-muted-foreground">Nenhuma transferência encontrada.</p>
                                            </TableCell>
                                        </TableRow>
                                        <TableRow v-for="withdrawal in (withdrawals?.data || [])" :key="withdrawal.id" class="cursor-pointer hover:bg-muted/50">
                                            <TableCell class="hidden sm:table-cell font-semibold">
                                                {{ formatCurrency(withdrawal.amount) }}
                                            </TableCell>
                                            <TableCell>
                                                <div class="space-y-1">
                                                    <p class="font-medium">{{ formatPixKey(withdrawal) }}</p>
                                                    <p class="text-xs text-muted-foreground sm:hidden">{{ getStatusLabel(withdrawal.status) }}</p>
                                                </div>
                                            </TableCell>
                                            <TableCell class="hidden sm:table-cell">
                                                <Badge variant="outline" :class="getStatusColor(withdrawal.status)">
                                                    {{ getStatusLabel(withdrawal.status) }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="hidden md:table-cell text-muted-foreground">
                                                {{ formatDate(withdrawal.created_at) }}
                                            </TableCell>
                                            <TableCell class="hidden lg:table-cell text-muted-foreground">
                                                {{ withdrawal.paid_at ? formatDate(withdrawal.paid_at) : 'Não informado' }}
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            
                            <!-- Paginação para Transferências -->
                            <div v-if="withdrawals && ((withdrawals.meta && withdrawals.meta.total > 10 && withdrawals.meta.last_page > 1) || (withdrawals.total > 10 && withdrawals.last_page > 1))" class="border-t border-border px-4 sm:px-6 py-2 bg-card">
                                <Pagination :pagination="withdrawals" />
                            </div>
                        </TabsContent>

                        <!-- Conteúdo da aba Antecipações -->
                        <TabsContent value="antecipacoes" class="p-4 sm:p-6 m-0">
                            <div class="text-center py-12">
                                <p class="text-muted-foreground">Nenhuma antecipação encontrada.</p>
                            </div>
                        </TabsContent>
                    </Tabs>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

