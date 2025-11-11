<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Search, Filter, Check, X, Clock, DollarSign, Wallet, CheckCircle, AlertCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Pagination } from '@/components/ui/pagination';
import { router, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

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
        title: 'Saques',
        href: '/admin/withdrawals',
    },
];

const props = defineProps({
    withdrawals: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            meta: {}
        })
    },
    summaryStats: {
        type: Object,
        default: () => ({
            totalAprovados: 0,
            quantidadeAprovados: 0,
            totalPendentes: 0,
            quantidadePendentes: 0,
            totalCancelados: 0,
            quantidadeCancelados: 0,
            quantidadeUsuarios: 0,
        })
    },
    saqueAutomatico: {
        type: Boolean,
        default: false
    }
});

// Dados reais do backend
const withdrawals = computed(() => props.withdrawals.data || []);
const saqueAutomatico = computed(() => props.saqueAutomatico || false);

const page = usePage();
const searchTerm = ref('');
const selectedWithdrawal = ref(null);
const isModalOpen = ref(false);

// Flash messages
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
const flashCancelled = computed(() => page.props.flash?.cancelled);

const filteredWithdrawals = computed(() => {
    if (!searchTerm.value) return withdrawals.value;
    
    const term = searchTerm.value.toLowerCase();
    return withdrawals.value.filter(withdrawal => 
        (withdrawal.email?.toLowerCase().includes(term))
    );
});

const summaryStats = computed(() => props.summaryStats);

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
        minute: '2-digit'
    });
};

const getStatusConfig = (status) => {
    const statusLower = String(status).toLowerCase();
    const configs = {
        aprovado: {
            label: 'Finalizado',
            color: 'bg-green-500/20 text-green-400 border-green-500/30'
        },
        pendente: {
            label: 'Pendente',
            color: 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30'
        },
        processando: {
            label: 'Processando',
            color: 'bg-blue-500/20 text-blue-400 border-blue-500/30'
        },
        cancelado: {
            label: 'Cancelado',
            color: 'bg-red-500/20 text-red-400 border-red-500/30'
        }
    };
    
    // Mapear variações de status
    if (statusLower === 'aprovado' || statusLower === 'done' || statusLower === 'done_manual' || statusLower === 'finalizado' || statusLower === 'paid' || statusLower === 'approved') {
        return configs.aprovado;
    }
    if (statusLower === 'processando' || statusLower === 'processing') {
        return configs.processando;
    }
    if (statusLower === 'cancelado' || statusLower === 'cancelled' || statusLower === 'failed' || statusLower === 'refused') {
        return configs.cancelado;
    }
    
    return configs.pendente;
};

const approveWithdrawal = (withdrawal) => {
    const form = useForm({});
    form.post(route('admin.withdrawals.approve', withdrawal.id), {
        preserveScroll: true,
    });
};

const cancelWithdrawal = (withdrawal) => {
    if (!confirm('Tem certeza que deseja cancelar este saque?')) return;
    
    const form = useForm({});
    form.post(route('admin.withdrawals.cancel', withdrawal.id), {
        preserveScroll: true,
    });
};

const openWithdrawalDetails = (withdrawal) => {
    selectedWithdrawal.value = withdrawal;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedWithdrawal.value = null;
};
</script>

<template>
    <Head title="Saques Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mt-2 sm:mt-4 flex h-full flex-1 flex-col gap-3 sm:gap-4 p-2 sm:p-4 max-w-7xl w-full mx-auto">
            <!-- Mensagens Flash -->
            <Alert v-if="flashSuccess" class="bg-green-500/10 border-green-500/50 text-green-400">
                <CheckCircle class="h-4 w-4" />
                <AlertTitle class="font-semibold">Sucesso!</AlertTitle>
                <AlertDescription>{{ flashSuccess }}</AlertDescription>
            </Alert>
            
            <Alert v-if="flashCancelled" variant="destructive" class="bg-red-500/10 border-red-500/50 text-red-400">
                <AlertCircle class="h-4 w-4" />
                <AlertTitle class="font-semibold">Cancelado!</AlertTitle>
                <AlertDescription>{{ flashCancelled }}</AlertDescription>
            </Alert>
            
            <Alert v-if="flashError" variant="destructive" class="bg-red-500/10 border-red-500/50 text-red-400">
                <AlertCircle class="h-4 w-4" />
                <AlertTitle class="font-semibold">Erro!</AlertTitle>
                <AlertDescription>{{ flashError }}</AlertDescription>
            </Alert>

            <!-- Cabeçalho -->
            <div class="space-y-1">
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Saques</h2>
                <p class="text-sm sm:text-base text-muted-foreground">
                    Acompanhe e gerencie todos os saques solicitados pelos vendedores da LuckPay.
                </p>
            </div>

            <!-- Cards de Resumo -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <Card class="border-border bg-card">
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs sm:text-sm text-muted-foreground mb-1">Saques Totais Aprovados</p>
                                <p class="text-xl sm:text-2xl font-bold text-foreground">{{ formatCurrency(summaryStats.totalAprovados) }}</p>
                                <p class="text-xs sm:text-sm text-muted-foreground mt-1">{{ summaryStats.quantidadeAprovados }} saques aprovados</p>
                            </div>
                            <div class="flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-lg bg-gray-500">
                                <DollarSign class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-border bg-card">
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs sm:text-sm text-muted-foreground mb-1">Saques Totais Pendentes</p>
                                <p class="text-xl sm:text-2xl font-bold text-foreground">{{ formatCurrency(summaryStats.totalPendentes) }}</p>
                                <p class="text-xs sm:text-sm text-muted-foreground mt-1">{{ summaryStats.quantidadePendentes }} saques pendentes</p>
                            </div>
                            <div class="flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-lg bg-gray-500">
                                <Clock class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-border bg-card">
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs sm:text-sm text-muted-foreground mb-1">Saques Totais Cancelados</p>
                                <p class="text-xl sm:text-2xl font-bold text-foreground">{{ formatCurrency(summaryStats.totalCancelados) }}</p>
                                <p class="text-xs sm:text-sm text-muted-foreground mt-1">{{ summaryStats.quantidadeCancelados }} saques cancelados</p>
                            </div>
                            <div class="flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-lg bg-gray-500">
                                <X class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-border bg-card">
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs sm:text-sm text-muted-foreground mb-1">Usuários com Saque</p>
                                <p class="text-xl sm:text-2xl font-bold text-foreground">{{ summaryStats.quantidadeUsuarios || 0 }}</p>
                                <p class="text-xs sm:text-sm text-muted-foreground mt-1">{{ summaryStats.quantidadeUsuarios === 1 ? 'usuário' : 'usuários' }} que solicitaram saque</p>
                            </div>
                            <div class="flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-lg bg-gray-500">
                                <Wallet class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filtros e Busca -->
            <Card class="w-full border-border bg-card">
                <CardHeader class="pb-4">
                    <CardTitle class="text-base sm:text-lg">Saques</CardTitle>
                    <div class="flex flex-col sm:flex-row gap-3 mt-4">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                            <Input
                                v-model="searchTerm"
                                placeholder="Buscar por e-mail..."
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
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="bg-muted/50 border-border hover:bg-muted/50">
                                    <TableHead class="font-semibold text-muted-foreground">E-mail do Usuário</TableHead>
                                    <TableHead class="font-semibold text-muted-foreground">Chave PIX</TableHead>
                                    <TableHead class="font-semibold text-muted-foreground">Valor</TableHead>
                                    <TableHead class="font-semibold text-muted-foreground">Data/Hora</TableHead>
                                    <TableHead class="font-semibold text-muted-foreground">Status</TableHead>
                                    <TableHead class="font-semibold text-muted-foreground">Ações</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="withdrawal in filteredWithdrawals"
                                    :key="withdrawal.id"
                                    class="border-border hover:bg-muted/50 transition-colors cursor-pointer"
                                    @click="openWithdrawalDetails(withdrawal)"
                                >
                                    <TableCell>
                                        <div>
                                            <div class="font-medium text-foreground">{{ withdrawal.email }}</div>
                                            <div class="text-xs text-muted-foreground">ID: {{ withdrawal.id }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div>
                                            <div class="text-foreground">{{ withdrawal.pixKey }}</div>
                                            <div class="text-xs text-muted-foreground">{{ withdrawal.pixType }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-semibold text-foreground">{{ formatCurrency(withdrawal.amount) }}</TableCell>
                                    <TableCell class="text-muted-foreground">{{ formatDate(withdrawal.date) }}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline" :class="getStatusConfig(withdrawal.status).color">
                                            {{ getStatusConfig(withdrawal.status).label }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell @click.stop>
                                        <div v-if="withdrawal.status === 'pendente' || withdrawal.status === 'pending'" class="flex items-center gap-2">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 hover:bg-green-500/10 hover:text-green-400"
                                                @click="approveWithdrawal(withdrawal)"
                                                :title="'Finalizar saque'"
                                            >
                                                <Check class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 hover:bg-red-500/10 hover:text-red-400"
                                                @click="cancelWithdrawal(withdrawal)"
                                                :title="'Cancelar saque'"
                                            >
                                                <X class="h-4 w-4" />
                                            </Button>
                                        </div>
                                        <span v-else class="text-xs text-muted-foreground">-</span>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
            
            <!-- Paginação -->
            <div v-if="props.withdrawals && ((props.withdrawals.meta && props.withdrawals.meta.total > 10 && props.withdrawals.meta.last_page > 1) || (props.withdrawals.total > 10 && props.withdrawals.last_page > 1))" class="border-t border-border w-full py-2 bg-card mt-4">
                <Pagination :pagination="props.withdrawals" />
            </div>

            <!-- Modal de Detalhes -->
            <Dialog :open="isModalOpen" @update:open="closeModal">
                <DialogContent class="sm:max-w-2xl bg-card border-border">
                    <DialogHeader v-if="selectedWithdrawal">
                        <DialogTitle class="text-xl">Detalhes do Saque</DialogTitle>
                        <DialogDescription class="text-base">
                            ID: {{ selectedWithdrawal.id }}
                        </DialogDescription>
                    </DialogHeader>
                    <div v-if="selectedWithdrawal" class="space-y-6 mt-4">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-semibold text-muted-foreground mb-2">E-mail do Usuário</h4>
                                <p class="text-foreground">{{ selectedWithdrawal.email }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-muted-foreground mb-2">ID do Saque</h4>
                                <p class="text-foreground">{{ selectedWithdrawal.id }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-muted-foreground mb-2">Chave PIX</h4>
                                <p class="text-foreground">{{ selectedWithdrawal.pixKey }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-muted-foreground mb-2">Tipo</h4>
                                <p class="text-foreground">{{ selectedWithdrawal.pixType }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-muted-foreground mb-2">Valor Solicitado</h4>
                                <p class="text-foreground font-semibold text-lg">{{ formatCurrency(selectedWithdrawal.amount) }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-muted-foreground mb-2">Status</h4>
                                <Badge variant="outline" :class="getStatusConfig(selectedWithdrawal.status).color">
                                    {{ getStatusConfig(selectedWithdrawal.status).label }}
                                </Badge>
                            </div>
                            <div class="col-span-2">
                                <h4 class="text-sm font-semibold text-muted-foreground mb-2">Data/Hora da Solicitação</h4>
                                <p class="text-foreground">{{ formatDate(selectedWithdrawal.date) }}</p>
                            </div>
                        </div>
                    </div>
                    <DialogFooter>
                        <div v-if="!saqueAutomatico && selectedWithdrawal && selectedWithdrawal.status === 'pendente'" class="flex gap-2">
                            <Button
                                variant="outline"
                                @click="approveWithdrawal(selectedWithdrawal); closeModal()"
                                class="border-green-500/30 text-green-400 hover:bg-green-500/10"
                            >
                                <Check class="h-4 w-4 mr-2" />
                                Aprovar
                            </Button>
                            <Button
                                variant="outline"
                                @click="cancelWithdrawal(selectedWithdrawal); closeModal()"
                                class="border-red-500/30 text-red-400 hover:bg-red-500/10"
                            >
                                <X class="h-4 w-4 mr-2" />
                                Cancelar
                            </Button>
                        </div>
                        <Button variant="outline" @click="closeModal" class="border-border hover:bg-accent hover:text-accent-foreground">
                            Fechar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

