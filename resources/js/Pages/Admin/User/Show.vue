<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs'
import {
    Mail,
    MapPin,
    CreditCard,
    TrendingUp,
    CheckCircle,
    XCircle,
    Clock,
    DollarSign,
    BarChart3,
    FileImage,
    Shield,
    Ban,
    Eye,
    Download,
    Wallet,
    ArrowUpRight,
    ArrowDownRight,
    Zap,
    Target,
    Edit,
    Settings,
} from 'lucide-vue-next'
import { router } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { route } from 'ziggy-js';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    stats: {
        type: Object,
        default: () => ({
            saldo_atual: 0,
            volume_transacionado: 0,
            transacoes_total: 0,
            transacoes_aprovadas: 0,
            depositos_aprovados: 0,
            depositos_liquidos: 0,
            pix_gerados: 0,
            lucro_plataforma: 0,
        })
    },
    acquirers: {
        type: Array,
        default: () => []
    },
    recentTransactions: {
        type: Array,
        default: () => []
    },
    globalTaxes: {
        type: Object,
        default: () => ({
            cash_in_percentage: 0,
            cash_in_fixed: 0,
            cash_out_percentage: 0,
            cash_out_fixed: 1.00,
        })
    },
    currentAcquirer: {
        type: Object,
        default: null
    }
});

const user = computed(() => props.user);
const stats = computed(() => props.stats);
const currentAcquirer = computed(() => props.currentAcquirer);
const globalTaxes = computed(() => props.globalTaxes);



const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Estados para edição das taxas
const taxDialogOpen = ref(false);
const editBalanceDialogOpen = ref(false);
const balanceEditAmount = ref(0);
const balanceEditType = ref('add'); // 'add' ou 'remove'

// Estados reativos para as taxas - inicializar com dados do usuário ou padrões do sistema
const editableTaxesData = ref({
    cashInPercentage: 0,
    cashInFixedValue: 1.00,
    cashOutPercentage: 0,
    cashOutFixedValue: 1.00,
});

// Função auxiliar para comparar valores (iguais = true)
const compareValues = (val1, val2) => {
    return Math.abs((parseFloat(val1) || 0) - (parseFloat(val2) || 0)) < 0.01;
};

// Computed para valores de Cash In exibidos
const displayedCashInPercentage = computed(() => {
    if (!user.value || !globalTaxes.value) return 0;
    const userVal = user.value.cash_in_percentage;
    const globalVal = parseFloat(globalTaxes.value.cash_in_percentage) || 0;
    
    // Se não tem valor salvo, usar global
    if (userVal === null || userVal === undefined || userVal === '') return globalVal;
    
    const userValParsed = parseFloat(userVal) || 0;
    
    // Se o valor salvo é igual à global, mostrar global
    if (compareValues(userValParsed, globalVal)) return globalVal;
    
    // Se é diferente, mostrar o valor personalizado
    return userValParsed;
});

const displayedCashInFixed = computed(() => {
    if (!user.value || !globalTaxes.value) return 0;
    const userVal = user.value.cash_in_fixed;
    const globalVal = parseFloat(globalTaxes.value.cash_in_fixed) || 0;
    
    if (userVal === null || userVal === undefined || userVal === '') return globalVal;
    
    const userValParsed = parseFloat(userVal) || 0;
    if (compareValues(userValParsed, globalVal)) return globalVal;
    return userValParsed;
});

// Computed para valores de Cash Out exibidos
const displayedCashOutPercentage = computed(() => {
    if (!user.value || !globalTaxes.value) return 0;
    const userVal = user.value.cash_out_percentage;
    const globalVal = parseFloat(globalTaxes.value.cash_out_percentage) || 0;
    
    if (userVal === null || userVal === undefined || userVal === '') return globalVal;
    
    const userValParsed = parseFloat(userVal) || 0;
    if (compareValues(userValParsed, globalVal)) return globalVal;
    return userValParsed;
});

const displayedCashOutFixed = computed(() => {
    if (!user.value || !globalTaxes.value) return 0;
    const userVal = user.value.cash_out_fixed;
    const globalVal = parseFloat(globalTaxes.value.cash_out_fixed) || 0;
    
    if (userVal === null || userVal === undefined || userVal === '') return globalVal;
    
    const userValParsed = parseFloat(userVal) || 0;
    if (compareValues(userValParsed, globalVal)) return globalVal;
    return userValParsed;
});

// Verificar se tem taxas personalizadas
const hasCustomCashIn = computed(() => {
    if (!user.value || !globalTaxes.value) return false;
    
    const userPct = user.value.cash_in_percentage;
    const userFixed = user.value.cash_in_fixed;
    const globalPct = parseFloat(globalTaxes.value.cash_in_percentage) || 0;
    const globalFixed = parseFloat(globalTaxes.value.cash_in_fixed) || 0;
    
    // Se ambos são null/undefined/vazios, não tem personalização
    if ((userPct === null || userPct === undefined || userPct === '') && 
        (userFixed === null || userFixed === undefined || userFixed === '')) {
        return false;
    }
    
    // Verificar se algum valor é diferente da global
    const userPctParsed = userPct !== null && userPct !== undefined && userPct !== '' ? parseFloat(userPct) || 0 : null;
    const userFixedParsed = userFixed !== null && userFixed !== undefined && userFixed !== '' ? parseFloat(userFixed) || 0 : null;
    
    return (userPctParsed !== null && !compareValues(userPctParsed, globalPct)) ||
           (userFixedParsed !== null && !compareValues(userFixedParsed, globalFixed));
});

const hasCustomCashOut = computed(() => {
    if (!user.value || !globalTaxes.value) return false;
    
    const userPct = user.value.cash_out_percentage;
    const userFixed = user.value.cash_out_fixed;
    const globalPct = parseFloat(globalTaxes.value.cash_out_percentage) || 0;
    const globalFixed = parseFloat(globalTaxes.value.cash_out_fixed) || 0;
    
    // Se ambos são null/undefined/vazios, não tem personalização
    if ((userPct === null || userPct === undefined || userPct === '') && 
        (userFixed === null || userFixed === undefined || userFixed === '')) {
        return false;
    }
    
    // Verificar se algum valor é diferente da global
    const userPctParsed = userPct !== null && userPct !== undefined && userPct !== '' ? parseFloat(userPct) || 0 : null;
    const userFixedParsed = userFixed !== null && userFixed !== undefined && userFixed !== '' ? parseFloat(userFixed) || 0 : null;
    
    return (userPctParsed !== null && !compareValues(userPctParsed, globalPct)) ||
           (userFixedParsed !== null && !compareValues(userFixedParsed, globalFixed));
});

// Inicializar com dados do usuário ou taxas globais
const initializeTaxes = () => {
    if (!user.value || !globalTaxes.value) return;
    
    // Sempre usar as taxas globais como base, mas se o usuário tiver valores diferentes, usar eles
    editableTaxesData.value = {
        cashInPercentage: hasCustomCashIn.value && user.value.cash_in_percentage !== null
            ? parseFloat(user.value.cash_in_percentage) 
            : (parseFloat(globalTaxes.value.cash_in_percentage) || 0),
        cashInFixedValue: hasCustomCashIn.value && user.value.cash_in_fixed !== null
            ? parseFloat(user.value.cash_in_fixed) 
            : (parseFloat(globalTaxes.value.cash_in_fixed) || 0),
        cashOutPercentage: hasCustomCashOut.value && user.value.cash_out_percentage !== null
            ? parseFloat(user.value.cash_out_percentage) 
            : (parseFloat(globalTaxes.value.cash_out_percentage) || 0),
        cashOutFixedValue: hasCustomCashOut.value && user.value.cash_out_fixed !== null
            ? parseFloat(user.value.cash_out_fixed) 
            : (parseFloat(globalTaxes.value.cash_out_fixed) || 0),
    };
};

// Inicializar quando o componente for montado ou quando user mudar
watch(() => user.value, () => {
    initializeTaxes();
}, { immediate: true });

const getStatusVariant = (status) => {
    switch (status?.toLowerCase()) {
        case 'active':
        case 'approved':
        case 'paid':
            return 'default';
        case 'pending':
            return 'secondary';
        case 'inactive':
        case 'rejected':
        case 'unpaid':
            return 'destructive';
        default:
            return 'outline';
    }
};

const getStatusIcon = (status) => {
    switch (status?.toLowerCase()) {
        case 'active':
        case 'approved':
        case 'paid':
            return CheckCircle;
        case 'pending':
            return Clock;
        case 'inactive':
        case 'rejected':
        case 'blocked':
            return XCircle;
        default:
            return Clock;
    }
};

const getStatusColor = (status) => {
    switch (status?.toLowerCase()) {
        case 'active':
            return 'bg-green-500';
        case 'pending':
            return 'bg-yellow-500';
        case 'inactive':
        case 'rejected':
        case 'blocked':
            return 'bg-red-500';
        default:
            return 'bg-gray-500';
    }
};

const getStatusText = (status) => {
    switch (status?.toLowerCase()) {
        case 'active':
            return 'Ativo';
        case 'pending':
            return 'Pendente';
        case 'inactive':
        case 'rejected':
        case 'blocked':
            return 'Bloqueado';
        default:
            return 'Pendente';
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR');
};

const saveTaxes = async () => {
    try {
        // Se as taxas forem iguais às globais, definir como null para usar taxas globais
        const dataToSave = {
            cash_in_percentage: compareValues(editableTaxesData.value.cashInPercentage, globalTaxes.value?.cash_in_percentage) 
                ? null 
                : editableTaxesData.value.cashInPercentage,
            cash_in_fixed: compareValues(editableTaxesData.value.cashInFixedValue, globalTaxes.value?.cash_in_fixed) 
                ? null 
                : editableTaxesData.value.cashInFixedValue,
            cash_out_percentage: compareValues(editableTaxesData.value.cashOutPercentage, globalTaxes.value?.cash_out_percentage) 
                ? null 
                : editableTaxesData.value.cashOutPercentage,
            cash_out_fixed: compareValues(editableTaxesData.value.cashOutFixedValue, globalTaxes.value?.cash_out_fixed) 
                ? null 
                : editableTaxesData.value.cashOutFixedValue,
        };
        
        await router.put(route('users.update', user.value.id), dataToSave);
        
        taxDialogOpen.value = false;
        toast.success('Taxas atualizadas com sucesso!');
        router.reload();
    } catch (error) {
        console.error('Erro ao salvar taxas:', error);
        toast.error('Erro ao salvar taxas. Tente novamente.');
    }
};

const resetTaxes = () => {
    // Resetar campos do formulário para taxas globais do sistema
    editableTaxesData.value = {
        cashInPercentage: globalTaxes.value?.cash_in_percentage ?? 0,
        cashInFixedValue: globalTaxes.value?.cash_in_fixed ?? 0,
        cashOutPercentage: globalTaxes.value?.cash_out_percentage ?? 0,
        cashOutFixedValue: globalTaxes.value?.cash_out_fixed ?? 0,
    };
};

const resetTaxesToGlobal = async () => {
    // Resetar para taxas globais do sistema e limpar valores personalizados do banco
    try {
        await router.put(route('users.update', user.value.id), {
            cash_in_percentage: null,
            cash_in_fixed: null,
            cash_out_percentage: null,
            cash_out_fixed: null,
        });
        taxDialogOpen.value = false;
        toast.success('Taxas resetadas para valores globais!');
        router.reload();
    } catch (error) {
        console.error('Erro ao resetar taxas:', error);
        toast.error('Erro ao resetar taxas');
    }
};

// Dados financeiros calculados
const additionalFinancialData = computed(() => ({
    average_transaction_value: stats.value.transacoes_aprovadas > 0 
        ? stats.value.depositos_liquidos / stats.value.transacoes_aprovadas 
        : 0
}));



// Função auxiliar para obter o token CSRF
const getCsrfToken = () => {
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (metaToken) return metaToken;
    
    // Tentar obter do cookie XSRF-TOKEN (padrão do Laravel)
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'XSRF-TOKEN') {
            return decodeURIComponent(value);
        }
    }
    return '';
};

const setUserStatus = async (status) => {
    if (status === 'active') {
        try {
            const response = await fetch(route('users.activate', props.user.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            if (data.success) {
                toast.success('Usuário aprovado com sucesso');
                router.reload();
            } else {
                toast.error(data.message || 'Erro ao aprovar usuário');
            }
        } catch (error) {
            console.error('Erro ao aprovar usuário:', error);
            toast.error('Erro ao aprovar usuário');
        }
    } else if (status === 'reject') {
        if (!confirm('Tem certeza que deseja rejeitar este usuário?')) return;
        try {
            const response = await fetch(route('users.reject', props.user.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            if (data.success) {
                toast.success('Usuário rejeitado com sucesso');
                router.reload();
            } else {
                toast.error(data.message || 'Erro ao rejeitar usuário');
            }
        } catch (error) {
            console.error('Erro ao rejeitar usuário:', error);
            toast.error('Erro ao rejeitar usuário');
        }
    } else if (status === 'blocked') {
        if (!confirm('Tem certeza que deseja bloquear este usuário?')) return;
        try {
            const response = await fetch(route('users.ban', props.user.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            if (data.success) {
                toast.success('Usuário bloqueado com sucesso');
                router.reload();
            } else {
                toast.error(data.message || 'Erro ao bloquear usuário');
            }
        } catch (error) {
            console.error('Erro ao bloquear usuário:', error);
            toast.error('Erro ao bloquear usuário');
        }
    }
};

const documentModalOpen = ref(false);
const documentModalUrl = ref('');
const documentModalIsPdf = ref(false);

const openDocumentModal = (url, isPdf = false) => {
    documentModalUrl.value = url;
    documentModalIsPdf.value = isPdf;
    documentModalOpen.value = true;
};

const closeDocumentModal = () => {
    documentModalOpen.value = false;
    documentModalUrl.value = '';
    documentModalIsPdf.value = false;
};

const updateUserAcquirer = (acquirerId) => {
    router.put(route('users.update', props.user.id), { 
        acquirer_id: acquirerId ? parseInt(acquirerId) : null 
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Adquirente atualizada com sucesso');
        },
        onError: () => {
            toast.error('Erro ao atualizar adquirente');
        },
    });
};

const updatePreferredAcquirer = (preferredAcquirer) => {
    router.put(route('users.update', props.user.id), { 
        preferred_acquirer: preferredAcquirer || null 
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Adquirente preferida atualizada com sucesso');
            router.reload();
        },
        onError: () => {
            toast.error('Erro ao atualizar adquirente preferida');
        },
    });
};

const saveBalanceEdit = async () => {
    if (!balanceEditAmount.value || balanceEditAmount.value <= 0) {
        toast.error('Informe um valor válido');
        return;
    }

    try {
        const amount = parseFloat(balanceEditAmount.value);
        await router.put(route('users.update-balance', props.user.id), {
            amount: amount,
            type: balanceEditType.value,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                editBalanceDialogOpen.value = false;
                balanceEditAmount.value = 0;
                toast.success(`Saldo ${balanceEditType.value === 'add' ? 'adicionado' : 'removido'} com sucesso!`);
                router.reload();
            },
            onError: (errors) => {
                toast.error(errors.message || 'Erro ao atualizar saldo');
            },
        });
    } catch (error) {
        console.error('Erro ao atualizar saldo:', error);
        toast.error('Erro ao atualizar saldo');
    }
};


</script>

<template>

    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 space-y-4 p-4 md:p-8 pt-6 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex items-center justify-between space-y-2">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight">{{ user.name }}</h2>
                    <p class="text-muted-foreground">
                        Visão geral das informações do usuário e transações
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button @click="setUserStatus('active')" v-if="user.status !== 'active'" variant="outline">
                        <Shield class="mr-2 h-4 w-4" />
                        Aprovar
                    </Button>
                    <Button @click="setUserStatus('reject')" v-if="user.status === 'pending'" variant="destructive">
                        <XCircle class="mr-2 h-4 w-4" />
                        Rejeitar
                    </Button>
                    <Button @click="setUserStatus('blocked')" v-if="user.status !== 'blocked' && user.status !== 'pending'" variant="destructive">
                        <Ban class="mr-2 h-4 w-4" />
                        Bloquear
                    </Button>
                </div>
            </div>

            <!-- User Profile Card -->
            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2 text-sm text-muted-foreground">
                                    <Mail class="h-4 w-4" />
                                    <span>{{ user.email }}</span>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <Badge :variant="getStatusVariant(user.status)"
                                        :class="getStatusColor(user.status) + ' text-white'">
                                        <component :is="getStatusIcon(user.status)" class="mr-1 h-3 w-3" />
                                        {{ getStatusText(user.status) }}
                                    </Badge>
                                    <span class="text-sm text-muted-foreground">
                                        Desde {{ formatDate(user.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Stats Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Saldo Atual</CardTitle>
                        <div class="flex items-center gap-2">
                            <DollarSign class="h-4 w-4 text-muted-foreground" />
                            <Button variant="ghost" size="sm" @click="editBalanceDialogOpen = true" class="h-6 w-6 p-0">
                                <Edit class="h-3 w-3" />
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(user.balance) }}</div>
                        <p class="text-xs text-muted-foreground">
                            Disponível para saque
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Volume Transacionado</CardTitle>
                        <BarChart3 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.volume_transacionado) }}</div>
                        <p class="text-xs text-muted-foreground">
                            Total processado
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Transações</CardTitle>
                        <CreditCard class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.transacoes_total || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            Transações realizadas
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Transações Aprovadas</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.transacoes_aprovadas || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            Taxa de aprovação: {{ stats.taxa_aprovacao || 0 }}%
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Financial Metrics -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Depósitos Aprovados</CardTitle>
                        <ArrowUpRight class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ formatCurrency(stats.depositos_aprovados) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.variacao_mensal >= 0 ? '+' : '' }}{{ stats.variacao_mensal || 0 }}% vs mês anterior
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Depósitos Líquidos</CardTitle>
                        <Wallet class="h-4 w-4 text-emerald-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-emerald-600">{{ formatCurrency(stats.depositos_liquidos)
                            }}</div>
                        <p class="text-xs text-muted-foreground">
                            Após taxas e tarifas
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">PIX Gerados</CardTitle>
                        <Zap class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ stats.pix_gerados }}</div>
                        <p class="text-xs text-muted-foreground">
                            Chaves PIX ativas
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Lucro Plataforma</CardTitle>
                        <Target class="h-4 w-4 text-yellow-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-yellow-600">{{ formatCurrency(stats.lucro_plataforma) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Receita gerada
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Configurações Financeiras -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <Settings class="h-5 w-5" />
                                Configurações Financeiras
                            </CardTitle>
                            <CardDescription>
                                Gerencie taxas e adquirente do usuário
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Seção de Adquirente -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <Label class="text-sm font-medium">Adquirente em Uso</Label>
                                <p class="text-xs text-muted-foreground">
                                    Selecione a adquirente preferida ou use a configuração global
                                </p>
                            </div>
                        </div>
                        <Select 
                            :model-value="user.preferred_acquirer || ''" 
                            @update:model-value="(value) => updatePreferredAcquirer(value)"
                        >
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Usar adquirente global" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">
                                    <div class="flex items-center gap-2">
                                        <span>Usar Adquirente Global</span>
                                        <Badge variant="outline" class="ml-2 text-xs">
                                            {{ currentAcquirer ? currentAcquirer.name : 'N/A' }}
                                        </Badge>
                                    </div>
                                </SelectItem>
                                <SelectItem value="liberpay">
                                    LiberPay
                                </SelectItem>
                                <SelectItem value="fullpix">
                                    FullPix
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <div class="p-3 bg-muted rounded-md">
                            <p class="text-xs text-muted-foreground">
                                <strong>Adquirente atual:</strong> 
                                {{ user.preferred_acquirer 
                                    ? (user.preferred_acquirer === 'liberpay' ? 'LiberPay' : 'FullPix') 
                                    : (currentAcquirer ? `Global: ${currentAcquirer.name}` : 'Nenhuma configurada') }}
                            </p>
                        </div>
                    </div>

                    <Separator />

                    <!-- Seção de Taxas -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <Label class="text-sm font-medium">Taxas de Cobrança</Label>
                                <p class="text-xs text-muted-foreground">
                                    Taxas aplicadas em pagamentos (Cash In) e saques (Cash Out)
                                </p>
                            </div>
                            <Dialog v-model:open="taxDialogOpen">
                                <DialogTrigger asChild>
                                    <Button variant="outline" size="sm" @click="initializeTaxes">
                                        <Edit class="mr-2 h-4 w-4" />
                                        Editar Taxas
                                    </Button>
                                </DialogTrigger>
                                <DialogContent class="sm:max-w-[600px]">
                                    <DialogHeader>
                                        <DialogTitle>Configurar Taxas Personalizadas</DialogTitle>
                                        <DialogDescription>
                                            Configure taxas específicas para este usuário. Deixe igual às taxas globais para usar as configurações do sistema.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <div class="grid gap-6 py-4">
                                        <!-- Cash In Settings -->
                                        <div class="space-y-4">
                                            <h4 class="font-medium flex items-center gap-2">
                                                <ArrowUpRight class="h-4 w-4 text-green-500" />
                                                Cash In (Pagamentos/Entradas)
                                            </h4>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label for="cashInPercentage">Percentual (%)</Label>
                                                    <Input 
                                                        id="cashInPercentage" 
                                                        type="number" 
                                                        step="0.01"
                                                        min="0"
                                                        max="100"
                                                        v-model.number="editableTaxesData.cashInPercentage" 
                                                    />
                                                    <p class="text-xs text-muted-foreground">
                                                        Taxa global: {{ globalTaxes?.cash_in_percentage ?? 0 }}%
                                                    </p>
                                                </div>
                                                <div class="space-y-2">
                                                    <Label for="cashInFixedValue">Valor Fixo (R$)</Label>
                                                    <Input 
                                                        id="cashInFixedValue" 
                                                        type="number" 
                                                        step="0.01"
                                                        min="0"
                                                        v-model.number="editableTaxesData.cashInFixedValue" 
                                                    />
                                                    <p class="text-xs text-muted-foreground">
                                                        Taxa global: {{ formatCurrency(globalTaxes?.cash_in_fixed ?? 0) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <Separator />

                                        <!-- Cash Out Settings -->
                                        <div class="space-y-4">
                                            <h4 class="font-medium flex items-center gap-2">
                                                <ArrowDownRight class="h-4 w-4 text-red-500" />
                                                Cash Out (Saques)
                                            </h4>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label for="cashOutPercentage">Percentual (%)</Label>
                                                    <Input 
                                                        id="cashOutPercentage" 
                                                        type="number" 
                                                        step="0.01"
                                                        min="0"
                                                        max="100"
                                                        v-model.number="editableTaxesData.cashOutPercentage" 
                                                    />
                                                    <p class="text-xs text-muted-foreground">
                                                        Taxa global: {{ globalTaxes?.cash_out_percentage ?? 0 }}%
                                                    </p>
                                                </div>
                                                <div class="space-y-2">
                                                    <Label for="cashOutFixedValue">Valor Fixo (R$)</Label>
                                                    <Input 
                                                        id="cashOutFixedValue" 
                                                        type="number" 
                                                        step="0.01"
                                                        min="0"
                                                        v-model.number="editableTaxesData.cashOutFixedValue" 
                                                    />
                                                    <p class="text-xs text-muted-foreground">
                                                        Taxa global: {{ formatCurrency(globalTaxes?.cash_out_fixed ?? 1.00) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <DialogFooter>
                                        <Button variant="outline" @click="resetTaxesToGlobal">
                                            Usar Taxas Globais
                                        </Button>
                                        <Button @click="saveTaxes">
                                            Salvar Taxas Personalizadas
                                        </Button>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        </div>

                        <!-- Exibição das Taxas Atuais -->
                        <div class="grid gap-4 md:grid-cols-2">
                            <Card>
                                <CardHeader class="pb-3">
                                    <CardTitle class="text-sm flex items-center gap-2">
                                        <ArrowUpRight class="h-4 w-4 text-green-500" />
                                        Cash In
                                    </CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Percentual:</span>
                                        <Badge variant="outline">
                                            {{ displayedCashInPercentage }}%
                                        </Badge>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Valor fixo:</span>
                                        <span>{{ formatCurrency(displayedCashInFixed) }}</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground mt-2">
                                        {{ hasCustomCashIn ? 'Taxa personalizada' : 'Usando taxa global do sistema' }}
                                    </p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="pb-3">
                                    <CardTitle class="text-sm flex items-center gap-2">
                                        <ArrowDownRight class="h-4 w-4 text-red-500" />
                                        Cash Out
                                    </CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Percentual:</span>
                                        <Badge variant="outline">
                                            {{ displayedCashOutPercentage }}%
                                        </Badge>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Valor fixo:</span>
                                        <span>{{ formatCurrency(displayedCashOutFixed) }}</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground mt-2">
                                        {{ hasCustomCashOut ? 'Taxa personalizada' : 'Usando taxa global do sistema' }}
                                    </p>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg flex items-center gap-2">
                            <Shield class="h-5 w-5" />
                            Informações da {{ user.person_type === 'pj' ? 'Empresa' : 'Pessoa' }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Informações Básicas -->
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-muted-foreground mb-1">Nome Completo</p>
                                    <p class="text-sm font-medium">{{ user.full_name || 'Não informado' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground mb-1">Tipo</p>
                                    <Badge variant="outline">
                                        {{ user.person_type === 'pj' ? 'Pessoa Jurídica' : 'Pessoa Física' }}
                                    </Badge>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-muted-foreground mb-1">{{ user.person_type === 'pj' ? 'CNPJ' : 'CPF' }}</p>
                                    <p class="text-sm font-medium">{{ user.document || 'Não informado' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground mb-1">Telefone</p>
                                    <p class="text-sm font-medium">{{ user.phone || 'Não informado' }}</p>
                                </div>
                            </div>

                            <div v-if="user.person_type === 'pj'">
                                <p class="text-xs text-muted-foreground mb-1">Razão Social</p>
                                <p class="text-sm font-medium">{{ user.social_reason || 'Não informado' }}</p>
                            </div>
                        </div>

                        <Separator />

                        <!-- Informações Financeiras -->
                        <div class="space-y-3">
                            <h4 class="text-sm font-semibold">Informações Financeiras</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-muted-foreground mb-1">Faturamento Médio</p>
                                    <p class="text-sm font-medium">{{ user.average_revenue || 'Não informado' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground mb-1">Ticket Médio</p>
                                    <p class="text-sm font-medium">{{ user.average_ticket || 'Não informado' }}</p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground mb-1">Produtos/Serviços</p>
                                <p class="text-sm font-medium">{{ user.products || 'Não informado' }}</p>
                            </div>
                        </div>

                        <Separator />

                        <!-- Endereço -->
                        <div class="space-y-3">
                            <h4 class="text-sm font-semibold">Endereço</h4>
                            <div v-for="address in user.addresses" :key="address.id" class="space-y-2">
                                <div class="text-sm">
                                    <p class="font-medium">{{ address.address }}, {{ address.number }}</p>
                                    <p class="text-muted-foreground">{{ address.city }} - {{ address.state }}</p>
                                    <p class="text-muted-foreground">CEP: {{ address.zip_code }}</p>
                                </div>
                            </div>
                            <div v-if="!user.addresses || user.addresses.length === 0" class="text-sm text-muted-foreground">
                                Nenhum endereço cadastrado
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg flex items-center gap-2">
                                <FileImage class="h-5 w-5" />
                                Documentos
                            </CardTitle>
                            <Button variant="ghost" size="sm">
                                <Download class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2" v-if="user.rg_cnh_frente">
                            <Label class="text-sm font-medium">Frente do RG/CNH</Label>
                            <div class="relative group overflow-hidden rounded-md border cursor-pointer" @click="openDocumentModal(`/storage/${user.rg_cnh_frente}`)">
                                <img :src="`/storage/${user.rg_cnh_frente}`" alt="Frente do documento"
                                    class="w-full h-20 object-cover transition-transform group-hover:scale-105" />
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                                    <Eye class="h-4 w-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2" v-if="user.rg_cnh_verso">
                            <Label class="text-sm font-medium">Verso do RG/CNH</Label>
                            <div class="relative group overflow-hidden rounded-md border cursor-pointer" @click="openDocumentModal(`/storage/${user.rg_cnh_verso}`)">
                                <img :src="`/storage/${user.rg_cnh_verso}`" alt="Verso do documento"
                                    class="w-full h-20 object-cover transition-transform group-hover:scale-105" />
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                                    <Eye class="h-4 w-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2" v-if="user.selfie">
                            <Label class="text-sm font-medium">Selfie com Documento</Label>
                            <div class="relative group overflow-hidden rounded-md border cursor-pointer" @click="openDocumentModal(`/storage/${user.selfie}`)">
                                <img :src="`/storage/${user.selfie}`" alt="Selfie com documento"
                                    class="w-full h-20 object-cover transition-transform group-hover:scale-105" />
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                                    <Eye class="h-4 w-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2" v-if="user.person_type === 'pj' && user.social_contract">
                            <Label class="text-sm font-medium">Contrato Social (PDF)</Label>
                            <div class="relative group overflow-hidden rounded-md border cursor-pointer" @click="openDocumentModal(`/storage/${user.social_contract}`, true)">
                                <div class="w-full h-20 bg-muted flex items-center justify-center">
                                    <FileImage class="h-8 w-8 text-muted-foreground" />
                                </div>
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                                    <Eye class="h-4 w-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Transactions Table -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Transações Recentes</CardTitle>
                            <CardDescription>
                                Últimas movimentações financeiras
                            </CardDescription>
                        </div>
                        <Button variant="outline" size="sm">
                            <Eye class="mr-2 h-4 w-4" />
                            Ver todas
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Método</TableHead>
                                <TableHead>Valor</TableHead>
                                <TableHead>Taxa</TableHead>
                                <TableHead>Data</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="transaction in recentTransactions" :key="transaction.id || transaction.transaction_id">
                                <TableCell class="font-medium">{{ transaction.transaction_id || transaction.id }}</TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusVariant(transaction.status || transaction.raw_status)">
                                        {{ transaction.status || 'N/A' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ transaction.payment_method || 'PIX' }}</TableCell>
                                <TableCell>{{ formatCurrency(transaction.total_amount || 0) }}</TableCell>
                                <TableCell class="text-red-500">{{ formatCurrency(transaction.fee || 0) }}</TableCell>
                                <TableCell>{{ formatDate(transaction.date) }}</TableCell>
                            </TableRow>
                            <TableRow v-if="!recentTransactions || recentTransactions.length === 0">
                                <TableCell colspan="6" class="text-center text-muted-foreground py-8">
                                    Nenhuma transação encontrada
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Modal de Visualização de Documento -->
            <Dialog :open="documentModalOpen" @update:open="closeDocumentModal">
                <DialogContent class="sm:max-w-4xl bg-card border-border">
                    <DialogHeader>
                        <DialogTitle>Visualizar Documento</DialogTitle>
                    </DialogHeader>
                    <div class="mt-4">
                        <div v-if="documentModalIsPdf" class="w-full h-[600px]">
                            <iframe 
                                :src="documentModalUrl" 
                                class="w-full h-full border border-border rounded-lg"
                                frameborder="0"
                            ></iframe>
                        </div>
                        <div v-else class="flex items-center justify-center">
                            <img 
                                :src="documentModalUrl" 
                                alt="Documento" 
                                class="max-w-full max-h-[600px] object-contain rounded-lg"
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="closeDocumentModal" class="border-border hover:bg-accent hover:text-accent-foreground">
                            Fechar
                        </Button>
                        <Button 
                            @click="window.open(documentModalUrl, '_blank')"
                            class="bg-primary text-primary-foreground hover:bg-primary/90"
                        >
                            <Download class="h-4 w-4 mr-2" />
                            Download
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Dialog de Edição de Saldo -->
            <Dialog v-model:open="editBalanceDialogOpen">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Editar Saldo</DialogTitle>
                        <DialogDescription>
                            Adicione ou remova saldo do usuário {{ user.name }}
                        </DialogDescription>
                    </DialogHeader>
                    <div class="grid gap-4 py-4">
                        <div class="space-y-2">
                            <Label>Tipo de Operação</Label>
                            <Select v-model="balanceEditType">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="add">Adicionar Saldo</SelectItem>
                                    <SelectItem value="remove">Remover Saldo</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Valor (R$)</Label>
                            <Input 
                                type="number" 
                                step="0.01" 
                                min="0.01"
                                v-model.number="balanceEditAmount"
                                placeholder="0,00"
                            />
                        </div>
                        <div class="p-3 bg-muted rounded-md">
                            <p class="text-sm text-muted-foreground">
                                Saldo atual: <strong>{{ formatCurrency(user.balance) }}</strong>
                            </p>
                            <p class="text-sm text-muted-foreground mt-1" v-if="balanceEditAmount > 0">
                                Saldo após operação: 
                                <strong>
                                    {{ formatCurrency(balanceEditType === 'add' ? (user.balance + balanceEditAmount) : Math.max(0, user.balance - balanceEditAmount)) }}
                                </strong>
                            </p>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="editBalanceDialogOpen = false">
                            Cancelar
                        </Button>
                        <Button @click="saveBalanceEdit" :disabled="!balanceEditAmount || balanceEditAmount <= 0">
                            {{ balanceEditType === 'add' ? 'Adicionar' : 'Remover' }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Smooth transitions */
* {
    transition: all 0.2s ease-in-out;
}
</style>