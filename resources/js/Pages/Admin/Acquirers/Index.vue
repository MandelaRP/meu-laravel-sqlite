<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { useToast } from '@/components/ui/toast/use-toast';
import Toaster from '@/components/ui/toast/Toaster.vue';
import { 
    CreditCard, 
    Power, 
    Settings, 
    CheckCircle2, 
    XCircle, 
    AlertCircle, 
    RefreshCw,
    Save,
    Eye,
    EyeOff
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    acquirers: {
        type: Array,
        default: () => []
    },
    activeAcquirer: {
        type: Object,
        default: null
    },
    gatewayPixFixed: {
        type: Number,
        default: 0.04
    },
    gatewayPixPercentage: {
        type: Number,
        default: 0
    }
});

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
        title: 'Adquirentes',
        href: '/admin/acquirers',
    },
];

const { toast } = useToast();
const page = usePage();

// Estado para diálogos e formulários
const editingAcquirer = ref(null);
const showCredentialsDialog = ref(false);
const showSettingsDialog = ref(false);
const settingsAcquirer = ref(null);
const showCredentials = ref({});

// Estado para toggle de ativar/desativar (local, não salva automaticamente)
const localAcquirerStates = ref({});

// Estado local para taxas de cada adquirente
const localFees = ref({});

// Inicializar estados locais com os dados do backend
watch(() => props.acquirers, (newAcquirers) => {
    if (newAcquirers && newAcquirers.length > 0) {
        newAcquirers.forEach(acquirer => {
            // Só atualizar se não existir no estado local (para não sobrescrever mudanças locais)
            if (localAcquirerStates.value[acquirer.id] === undefined) {
                localAcquirerStates.value[acquirer.id] = acquirer.is_active;
            }
            // Inicializar taxas locais
            if (!localFees.value[acquirer.id]) {
                localFees.value[acquirer.id] = {
                    fixed_fee: acquirer.fixed_fee || 0,
                    percentage_fee: acquirer.percentage_fee || 0,
                    withdrawal_fee: acquirer.withdrawal_fee || 0
                };
            }
        });
        // Forçar reatividade
        localAcquirerStates.value = { ...localAcquirerStates.value };
        localFees.value = { ...localFees.value };
    }
}, { immediate: true });

// Formulário para atualizar adquirente
const updateForm = useForm({
    credentials: {},
    settings: {},
    fixed_fee: 0,
    percentage_fee: 0
});

// Formulário para salvar taxas
const feeForm = useForm({
    fixed_fee: 0,
    percentage_fee: 0,
    withdrawal_fee: 0
});

// Formulário para salvar estado ativo/desativado
const toggleForm = useForm({
    is_active: false
});

// Função para obter cor do badge de status (considera is_active)
const getStatusColor = (status, isActive) => {
    // Se não estiver ativa, sempre vermelho
    if (!isActive && status !== 'checking') {
        return 'bg-red-500';
    }
    
    // Se estiver verificando, amarelo
    if (status === 'checking') {
        return 'bg-yellow-500';
    }
    
    // Se estiver ativa e online, verde
    if (isActive && status === 'online') {
        return 'bg-green-500';
    }
    
    // Se estiver ativa mas offline/erro, vermelho
    if (isActive && (status === 'offline' || status === 'error')) {
        return 'bg-red-500';
    }
    
    // Padrão: cinza
    return 'bg-gray-500';
};

// Função para obter texto do status
const getStatusText = (status, isActive) => {
    // Se não estiver ativa, mostrar como Desativada
    if (!isActive && status !== 'checking') {
        return 'Desativada';
    }
    
    const texts = {
        online: 'Ativo',
        offline: 'Desativada',
        error: 'Erro',
        checking: 'Verificando...'
    };
    return texts[status] || 'Desconhecido';
};

// Função para obter ícone do status
const getStatusIcon = (status) => {
    if (status === 'online') return CheckCircle2;
    if (status === 'error') return XCircle;
    if (status === 'checking') return RefreshCw;
    return AlertCircle;
};

// Ativar adquirente (desativa todas as outras automaticamente)
const activateAcquirer = (acquirerId) => {
    toggleForm.is_active = true;
    toggleForm.put(route('admin.acquirers.update-toggle', acquirerId), {
        preserveScroll: true,
        only: ['acquirers', 'activeAcquirer'],
        onSuccess: (page) => {
            const successMessage = page.props.flash?.success;
            // Atualizar estado local com os dados do backend
            if (page.props.acquirers) {
                page.props.acquirers.forEach(acquirer => {
                    localAcquirerStates.value[acquirer.id] = acquirer.is_active;
                });
                localAcquirerStates.value = { ...localAcquirerStates.value };
            }
            toast({
                title: 'Sucesso!',
                description: successMessage || 'Adquirente ativada com sucesso.',
            });
        },
        onError: (errors) => {
            const errorMessage = errors.message || errors.error || 'Não foi possível ativar a adquirente.';
            toast({
                title: 'Erro!',
                description: errorMessage,
                variant: 'destructive',
            });
        },
    });
};

// Desativar adquirente
const deactivateAcquirer = (acquirerId) => {
    toggleForm.is_active = false;
    toggleForm.put(route('admin.acquirers.update-toggle', acquirerId), {
        preserveScroll: true,
        only: ['acquirers', 'activeAcquirer'],
        onSuccess: (page) => {
            const successMessage = page.props.flash?.success;
            // Atualizar estado local com os dados do backend
            if (page.props.acquirers) {
                page.props.acquirers.forEach(acquirer => {
                    localAcquirerStates.value[acquirer.id] = acquirer.is_active;
                });
                localAcquirerStates.value = { ...localAcquirerStates.value };
            }
            toast({
                title: 'Sucesso!',
                description: successMessage || 'Adquirente desativada com sucesso.',
            });
        },
        onError: (errors) => {
            const errorMessage = errors.message || errors.error || 'Não foi possível desativar a adquirente.';
            toast({
                title: 'Erro!',
                description: errorMessage,
                variant: 'destructive',
            });
        },
    });
};

// Verificar status da API
const checkApiStatus = (acquirerId) => {
    router.post(route('admin.acquirers.check-status', acquirerId), {}, {
        preserveScroll: false,
        only: ['acquirers'],
        onSuccess: (page) => {
            const successMessage = page.props.flash?.success;
            const errorMessage = page.props.flash?.error;
            
            // Atualizar estados locais com os novos dados
            if (page.props.acquirers) {
                page.props.acquirers.forEach(acquirer => {
                    localAcquirerStates.value[acquirer.id] = acquirer.is_active;
                });
            }
            
            if (errorMessage) {
                toast({
                    title: 'Erro!',
                    description: errorMessage,
                    variant: 'destructive',
                });
            } else if (successMessage) {
                toast({
                    title: 'Status verificado!',
                    description: successMessage,
                });
            }
        },
        onError: (errors) => {
            const errorMessage = errors.message || errors.error || 'Não foi possível verificar o status da API.';
            toast({
                title: 'Erro!',
                description: errorMessage,
                variant: 'destructive',
            });
        },
    });
};

// Abrir diálogo de configurações
const openSettingsDialog = (acquirer) => {
    settingsAcquirer.value = acquirer;
    showSettingsDialog.value = true;
};

// Abrir diálogo de edição de credenciais
const openEditDialog = (acquirer) => {
    editingAcquirer.value = acquirer;
    // Garantir que os campos existam baseado no slug da adquirente
    if (acquirer.slug === 'fullpix') {
        // FullPix usa secret_key e public_key
        updateForm.credentials = {
            secret_key: acquirer.credentials?.secret_key || '',
            public_key: acquirer.credentials?.public_key || '',
        };
    } else {
        // LiberPay usa chave_publica, chave_privada e chave_saque_externo
        updateForm.credentials = {
            chave_publica: acquirer.credentials?.chave_publica || '',
            chave_privada: acquirer.credentials?.chave_privada || '',
            chave_saque_externo: acquirer.credentials?.chave_saque_externo || '',
        };
    }
    updateForm.settings = acquirer.settings || {};
    updateForm.fixed_fee = acquirer.fixed_fee || 0;
    updateForm.percentage_fee = acquirer.percentage_fee || 0;
    showCredentialsDialog.value = true;
};


// Salvar taxas da adquirente
const saveAcquirerFees = (acquirerId) => {
    const localFee = localFees.value[acquirerId];
    if (!localFee) return;
    
    feeForm.fixed_fee = localFee.fixed_fee || 0;
    feeForm.percentage_fee = localFee.percentage_fee || 0;
    feeForm.withdrawal_fee = localFee.withdrawal_fee || 0;
    
    feeForm.post(route('admin.acquirers.update', acquirerId), {
        preserveScroll: true,
        only: ['acquirers', 'activeAcquirer'],
        onSuccess: (page) => {
            const successMessage = page.props.flash?.success;
            // Atualizar taxas locais com os dados do backend
            if (page.props.acquirers) {
                page.props.acquirers.forEach(acquirer => {
                    if (localFees.value[acquirer.id]) {
                        localFees.value[acquirer.id] = {
                            fixed_fee: acquirer.fixed_fee || 0,
                            percentage_fee: acquirer.percentage_fee || 0,
                            withdrawal_fee: acquirer.withdrawal_fee || 0
                        };
                    }
                });
                localFees.value = { ...localFees.value };
            }
            toast({
                title: 'Sucesso!',
                description: successMessage || 'Taxas salvas com sucesso.',
            });
        },
        onError: (errors) => {
            const errorMessage = errors.message || 'Não foi possível salvar as taxas.';
            toast({
                title: 'Erro!',
                description: errorMessage,
                variant: 'destructive',
            });
        }
    });
};

// Salvar configurações
const saveAcquirerSettings = () => {
    if (!editingAcquirer.value) return;

    updateForm.post(route('admin.acquirers.update', editingAcquirer.value.id), {
        preserveScroll: true,
        only: ['acquirers'],
        onSuccess: (page) => {
            const successMessage = page.props.flash?.success;
            toast({
                title: 'Sucesso!',
                description: successMessage || 'Configurações salvas com sucesso.',
            });
            showCredentialsDialog.value = false;
            editingAcquirer.value = null;
        },
        onError: (errors) => {
            const errorMessage = errors.message || 'Não foi possível salvar as configurações.';
            toast({
                title: 'Erro!',
                description: errorMessage,
                variant: 'destructive',
            });
        }
    });
};


// Toggle mostrar/ocultar credenciais
const toggleShowCredentials = (credentialKey) => {
    // Inicializar se não existir
    if (showCredentials.value[credentialKey] === undefined) {
        showCredentials.value[credentialKey] = false;
    }
    showCredentials.value[credentialKey] = !showCredentials.value[credentialKey];
    // Forçar reatividade Vue
    showCredentials.value = { ...showCredentials.value };
};
</script>

<template>
    <Head title="Adquirentes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mt-2 sm:mt-4 flex h-full flex-1 flex-col gap-6 p-2 sm:p-4 w-full mx-auto">
            <!-- Cabeçalho -->
            <div class="space-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground mb-1">Bem-vindo, Admin</p>
                        <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Adquirentes</h2>
                        <p class="text-sm sm:text-base text-muted-foreground mt-1">
                            Gerencie as adquirentes de pagamento do sistema.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Área Informativa -->
            <Card class="border-border bg-card rounded-2xl shadow-md mb-6">
                <CardHeader>
                    <CardTitle class="text-lg">Informações da Adquirente Ativa</CardTitle>
                    <CardDescription>
                        Visualize as configurações da adquirente em uso e as taxas aplicadas
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="activeAcquirer" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <Label class="text-sm font-medium text-muted-foreground">Adquirente Ativa</Label>
                                <p class="text-lg font-semibold">{{ activeAcquirer.name }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label class="text-sm font-medium text-muted-foreground">Taxa Fixa</Label>
                                <p class="text-lg font-semibold">R$ {{ (activeAcquirer.fixed_fee || 0).toFixed(2) }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label class="text-sm font-medium text-muted-foreground">Taxa Percentual</Label>
                                <p class="text-lg font-semibold">{{ (activeAcquirer.percentage_fee || 0).toFixed(2) }}%</p>
                            </div>
                        </div>
                        <div class="border-t pt-4">
                            <Label class="text-sm font-medium text-muted-foreground mb-2 block">Taxa da LuckPay (Gateway)</Label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <Label class="text-xs text-muted-foreground">Taxa Fixa</Label>
                                    <p class="text-base font-semibold">R$ {{ gatewayPixFixed.toFixed(2) }}</p>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-xs text-muted-foreground">Taxa Percentual</Label>
                                    <p class="text-base font-semibold">{{ gatewayPixPercentage.toFixed(2) }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-4">
                        <p class="text-muted-foreground">Nenhuma adquirente ativa no momento.</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Grid de Cards de Adquirentes (Horizontal) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <Card 
                    v-for="acquirer in acquirers" 
                    :key="acquirer.id"
                    class="border-border bg-card rounded-2xl shadow-md"
                >
                    <CardHeader class="pb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary">
                                    <CreditCard class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <CardTitle class="text-base sm:text-lg">{{ acquirer.name }}</CardTitle>
                                    <CardDescription class="text-sm mt-1">
                                        {{ acquirer.description || 'Adquirente de pagamento' }}
                                    </CardDescription>
                                </div>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Status da API -->
                        <div class="flex items-center justify-between">
                            <Label class="text-sm font-medium">Status da API</Label>
                            <div class="flex items-center gap-2">
                                <Badge :class="[getStatusColor(acquirer.api_status, acquirer.is_active), 'text-white']">
                                    <component :is="getStatusIcon(acquirer.api_status)" class="h-3 w-3 mr-1" />
                                    {{ getStatusText(acquirer.api_status, acquirer.is_active) }}
                                </Badge>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="checkApiStatus(acquirer.id)"
                                    :disabled="acquirer.api_status === 'checking'"
                                >
                                    <RefreshCw 
                                        class="h-4 w-4" 
                                        :class="{ 'animate-spin': acquirer.api_status === 'checking' }"
                                    />
                                </Button>
                            </div>
                        </div>

                        <!-- Botões Ativar/Desativar -->
                        <div class="space-y-2">
                            <div class="flex gap-2">
                                <Button
                                    v-if="!acquirer.is_active"
                                    size="sm"
                                    variant="default"
                                    class="flex-1"
                                    @click="activateAcquirer(acquirer.id)"
                                    :disabled="toggleForm.processing"
                                >
                                    <Power class="h-3 w-3 mr-2" />
                                    Ativar
                                </Button>
                                <Button
                                    v-else
                                    size="sm"
                                    variant="destructive"
                                    class="flex-1"
                                    @click="deactivateAcquirer(acquirer.id)"
                                    :disabled="toggleForm.processing"
                                >
                                    <Power class="h-3 w-3 mr-2" />
                                    Desativar
                                </Button>
                            </div>
                        </div>

                        <!-- Botão Configurações -->
                        <Button
                            variant="outline"
                            class="w-full"
                            @click="openSettingsDialog(acquirer)"
                        >
                            <Settings class="h-4 w-4 mr-2" />
                            Configurações
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Dialog de Configurações -->
            <Dialog v-model:open="showSettingsDialog">
                <DialogContent class="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Configurações - {{ settingsAcquirer?.name }}</DialogTitle>
                        <DialogDescription>
                            Configure credenciais e taxas da adquirente.
                        </DialogDescription>
                    </DialogHeader>
                    <div v-if="settingsAcquirer" class="space-y-6">
                        <!-- Credenciais -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <Label class="text-base font-semibold">Credenciais</Label>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="openEditDialog(settingsAcquirer)"
                                >
                                    <Settings class="h-4 w-4 mr-2" />
                                    Editar Credenciais
                                </Button>
                            </div>
                            <div class="space-y-3">
                                <!-- FullPix: Secret Key e Public Key -->
                                <template v-if="settingsAcquirer.slug === 'fullpix'">
                                    <!-- Secret Key -->
                                    <div v-if="settingsAcquirer.credentials && settingsAcquirer.credentials.secret_key" class="space-y-1">
                                        <Label class="text-xs text-muted-foreground">Secret Key</Label>
                                        <div class="flex items-center gap-2">
                                            <Input
                                                :value="showCredentials[settingsAcquirer.id + '-secret_key'] ? (settingsAcquirer.credentials.secret_key || '') : '••••••••••••••••••••••••'"
                                                readonly
                                                class="bg-background border-border flex-1 font-mono text-xs"
                                            />
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                type="button"
                                                @click="toggleShowCredentials(settingsAcquirer.id + '-secret_key')"
                                            >
                                                <Eye v-if="!showCredentials[settingsAcquirer.id + '-secret_key']" class="h-4 w-4" />
                                                <EyeOff v-else class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <div v-else-if="settingsAcquirer.credentials" class="space-y-1">
                                        <Label class="text-xs text-muted-foreground">Secret Key</Label>
                                        <Input
                                            value="Não configurada"
                                            readonly
                                            disabled
                                            class="bg-muted border-border flex-1 font-mono text-xs opacity-50"
                                        />
                                    </div>

                                    <!-- Public Key -->
                                    <div v-if="settingsAcquirer.credentials && settingsAcquirer.credentials.public_key" class="space-y-1">
                                        <Label class="text-xs text-muted-foreground">Public Key</Label>
                                        <div class="flex items-center gap-2">
                                            <Input
                                                :value="showCredentials[settingsAcquirer.id + '-public_key'] ? (settingsAcquirer.credentials.public_key || '') : '••••••••••••••••••••••••'"
                                                readonly
                                                class="bg-background border-border flex-1 font-mono text-xs"
                                            />
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                type="button"
                                                @click="toggleShowCredentials(settingsAcquirer.id + '-public_key')"
                                            >
                                                <Eye v-if="!showCredentials[settingsAcquirer.id + '-public_key']" class="h-4 w-4" />
                                                <EyeOff v-else class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <div v-else-if="settingsAcquirer.credentials" class="space-y-1">
                                        <Label class="text-xs text-muted-foreground">Public Key</Label>
                                        <Input
                                            value="Não configurada"
                                            readonly
                                            disabled
                                            class="bg-muted border-border flex-1 font-mono text-xs opacity-50"
                                        />
                                    </div>
                                </template>

                                <!-- LiberPay: Chave Pública, Chave Privada e Chave de Saque Externo -->
                                <template v-else>
                                    <!-- Chave Privada -->
                                    <div v-if="settingsAcquirer.credentials && settingsAcquirer.credentials.chave_privada" class="space-y-1">
                                        <Label class="text-xs text-muted-foreground">Chave Privada</Label>
                                        <div class="flex items-center gap-2">
                                            <Input
                                                :value="showCredentials[settingsAcquirer.id + '-chave_privada'] ? (settingsAcquirer.credentials.chave_privada || '') : '••••••••••••••••••••••••'"
                                                readonly
                                                class="bg-background border-border flex-1 font-mono text-xs"
                                            />
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                type="button"
                                                @click="toggleShowCredentials(settingsAcquirer.id + '-chave_privada')"
                                            >
                                                <Eye v-if="!showCredentials[settingsAcquirer.id + '-chave_privada']" class="h-4 w-4" />
                                                <EyeOff v-else class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <div v-else-if="settingsAcquirer.credentials" class="space-y-1">
                                        <Label class="text-xs text-muted-foreground">Chave Privada</Label>
                                        <Input
                                            value="Não configurada"
                                            readonly
                                            disabled
                                            class="bg-muted border-border flex-1 font-mono text-xs opacity-50"
                                        />
                                    </div>

                                    <!-- Chave Pública -->
                                    <div v-if="settingsAcquirer.credentials && settingsAcquirer.credentials.chave_publica" class="space-y-1">
                                        <Label class="text-xs text-muted-foreground">Chave Pública</Label>
                                        <div class="flex items-center gap-2">
                                            <Input
                                                :value="showCredentials[settingsAcquirer.id + '-chave_publica'] ? (settingsAcquirer.credentials.chave_publica || '') : '••••••••••••••••••••••••'"
                                                readonly
                                                class="bg-background border-border flex-1 font-mono text-xs"
                                            />
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                type="button"
                                                @click="toggleShowCredentials(settingsAcquirer.id + '-chave_publica')"
                                            >
                                                <Eye v-if="!showCredentials[settingsAcquirer.id + '-chave_publica']" class="h-4 w-4" />
                                                <EyeOff v-else class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <div v-else-if="settingsAcquirer.credentials" class="space-y-1">
                                        <Label class="text-xs text-muted-foreground">Chave Pública</Label>
                                        <Input
                                            value="Não configurada"
                                            readonly
                                            disabled
                                            class="bg-muted border-border flex-1 font-mono text-xs opacity-50"
                                        />
                                    </div>

                                    <!-- Chave de Saque Externo (apenas LiberPay) -->
                                    <div v-if="settingsAcquirer.credentials && settingsAcquirer.credentials.chave_saque_externo" class="space-y-1">
                                        <Label class="text-xs text-muted-foreground">Chave de Saque Externo</Label>
                                        <div class="flex items-center gap-2">
                                            <Input
                                                :value="showCredentials[settingsAcquirer.id + '-chave_saque_externo'] ? (settingsAcquirer.credentials.chave_saque_externo || '') : '••••••••••••••••••••••••'"
                                                readonly
                                                class="bg-background border-border flex-1 font-mono text-xs"
                                            />
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                type="button"
                                                @click="toggleShowCredentials(settingsAcquirer.id + '-chave_saque_externo')"
                                            >
                                                <Eye v-if="!showCredentials[settingsAcquirer.id + '-chave_saque_externo']" class="h-4 w-4" />
                                                <EyeOff v-else class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <div v-else-if="settingsAcquirer.credentials" class="space-y-1">
                                        <Label class="text-xs text-muted-foreground">Chave de Saque Externo</Label>
                                        <Input
                                            value="Não configurada"
                                            readonly
                                            disabled
                                            class="bg-muted border-border flex-1 font-mono text-xs opacity-50"
                                        />
                                    </div>
                                </template>
                                
                                <!-- Mensagem se não houver credenciais -->
                                <div v-if="!settingsAcquirer.credentials || Object.keys(settingsAcquirer.credentials || {}).length === 0" class="text-xs text-muted-foreground">
                                    Nenhuma credencial configurada. Clique em "Editar Credenciais" para adicionar.
                                </div>
                            </div>
                        </div>

                        <!-- Taxas da Adquirente -->
                        <div class="space-y-3 border-t pt-4">
                            <Label class="text-base font-semibold">Taxas da Adquirente</Label>
                            <div class="space-y-3">
                                <div class="space-y-2">
                                    <Label for="fixed_fee_settings" class="text-xs text-muted-foreground">Taxa Fixa (R$)</Label>
                                    <Input
                                        :id="`fixed_fee_settings_${settingsAcquirer.id}`"
                                        :model-value="localFees[settingsAcquirer.id]?.fixed_fee || 0"
                                        @update:model-value="(value) => { if (!localFees[settingsAcquirer.id]) localFees[settingsAcquirer.id] = {}; localFees[settingsAcquirer.id].fixed_fee = parseFloat(value) || 0; }"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        class="bg-background border-border"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label for="percentage_fee_settings" class="text-xs text-muted-foreground">Taxa Percentual (%)</Label>
                                    <Input
                                        :id="`percentage_fee_settings_${settingsAcquirer.id}`"
                                        :model-value="localFees[settingsAcquirer.id]?.percentage_fee || 0"
                                        @update:model-value="(value) => { if (!localFees[settingsAcquirer.id]) localFees[settingsAcquirer.id] = {}; localFees[settingsAcquirer.id].percentage_fee = parseFloat(value) || 0; }"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        placeholder="0.00"
                                        class="bg-background border-border"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label for="withdrawal_fee_settings" class="text-xs text-muted-foreground">Taxa de Saque (R$)</Label>
                                    <Input
                                        :id="`withdrawal_fee_settings_${settingsAcquirer.id}`"
                                        :model-value="localFees[settingsAcquirer.id]?.withdrawal_fee || 0"
                                        @update:model-value="(value) => { if (!localFees[settingsAcquirer.id]) localFees[settingsAcquirer.id] = {}; localFees[settingsAcquirer.id].withdrawal_fee = parseFloat(value) || 0; }"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        class="bg-background border-border"
                                    />
                                    <p class="text-xs text-muted-foreground">Taxa cobrada pela adquirente em cada saque (ex: R$ 0,50 para FullPix)</p>
                                </div>
                                <Button
                                    size="sm"
                                    variant="default"
                                    class="w-full"
                                    @click="saveAcquirerFees(settingsAcquirer.id)"
                                    :disabled="feeForm.processing"
                                >
                                    <Save class="h-3 w-3 mr-2" />
                                    Salvar Taxas
                                </Button>
                            </div>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>

            <!-- Dialog de Editar Credenciais -->
            <Dialog v-model:open="showCredentialsDialog">
                            <DialogContent class="sm:max-w-[500px]">
                                <DialogHeader>
                                    <DialogTitle>Configurar Credenciais - {{ editingAcquirer?.name }}</DialogTitle>
                                    <DialogDescription>
                                        Configure as chaves de API da adquirente. As chaves são armazenadas de forma segura.
                                    </DialogDescription>
                                </DialogHeader>
                                <div v-if="editingAcquirer" class="space-y-4">
                                    <div class="space-y-4">
                                        <!-- FullPix: Secret Key e Public Key -->
                                        <template v-if="editingAcquirer.slug === 'fullpix'">
                                            <!-- Secret Key -->
                                            <div class="space-y-2">
                                                <Label for="secret_key" class="text-sm font-medium">Secret Key *</Label>
                                                <div class="flex items-center gap-2">
                                                    <Input
                                                        id="secret_key"
                                                        v-model="updateForm.credentials.secret_key"
                                                        :type="showCredentials[`edit-${editingAcquirer.id}-secret_key`] ? 'text' : 'password'"
                                                        :placeholder="showCredentials[`edit-${editingAcquirer.id}-secret_key`] ? 'Cole sua Secret Key aqui' : '••••••••••••••••••••••••'"
                                                        class="bg-background border-border flex-1 font-mono text-xs"
                                                    />
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        type="button"
                                                        @click="showCredentials[`edit-${editingAcquirer.id}-secret_key`] = !showCredentials[`edit-${editingAcquirer.id}-secret_key`]"
                                                    >
                                                        <Eye v-if="!showCredentials[`edit-${editingAcquirer.id}-secret_key`]" class="h-4 w-4" />
                                                        <EyeOff v-else class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </div>

                                            <!-- Public Key -->
                                            <div class="space-y-2">
                                                <Label for="public_key" class="text-sm font-medium">Public Key *</Label>
                                                <div class="flex items-center gap-2">
                                                    <Input
                                                        id="public_key"
                                                        v-model="updateForm.credentials.public_key"
                                                        :type="showCredentials[`edit-${editingAcquirer.id}-public_key`] ? 'text' : 'password'"
                                                        :placeholder="showCredentials[`edit-${editingAcquirer.id}-public_key`] ? 'Cole sua Public Key aqui' : '••••••••••••••••••••••••'"
                                                        class="bg-background border-border flex-1 font-mono text-xs"
                                                    />
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        type="button"
                                                        @click="showCredentials[`edit-${editingAcquirer.id}-public_key`] = !showCredentials[`edit-${editingAcquirer.id}-public_key`]"
                                                    >
                                                        <Eye v-if="!showCredentials[`edit-${editingAcquirer.id}-public_key`]" class="h-4 w-4" />
                                                        <EyeOff v-else class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- LiberPay: Chave Pública, Chave Privada e Chave de Saque Externo -->
                                        <template v-else>
                                            <!-- Chave Privada -->
                                            <div class="space-y-2">
                                                <Label for="chave_privada" class="text-sm font-medium">Chave Privada *</Label>
                                                <div class="flex items-center gap-2">
                                                    <Input
                                                        id="chave_privada"
                                                        v-model="updateForm.credentials.chave_privada"
                                                        :type="showCredentials[`edit-${editingAcquirer.id}-chave_privada`] ? 'text' : 'password'"
                                                        :placeholder="showCredentials[`edit-${editingAcquirer.id}-chave_privada`] ? 'Cole sua chave privada aqui' : '••••••••••••••••••••••••'"
                                                        class="bg-background border-border flex-1 font-mono text-xs"
                                                    />
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        type="button"
                                                        @click="showCredentials[`edit-${editingAcquirer.id}-chave_privada`] = !showCredentials[`edit-${editingAcquirer.id}-chave_privada`]"
                                                    >
                                                        <Eye v-if="!showCredentials[`edit-${editingAcquirer.id}-chave_privada`]" class="h-4 w-4" />
                                                        <EyeOff v-else class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </div>

                                            <!-- Chave Pública -->
                                            <div class="space-y-2">
                                                <Label for="chave_publica" class="text-sm font-medium">Chave Pública *</Label>
                                                <div class="flex items-center gap-2">
                                                    <Input
                                                        id="chave_publica"
                                                        v-model="updateForm.credentials.chave_publica"
                                                        :type="showCredentials[`edit-${editingAcquirer.id}-chave_publica`] ? 'text' : 'password'"
                                                        :placeholder="showCredentials[`edit-${editingAcquirer.id}-chave_publica`] ? 'Cole sua chave pública aqui' : '••••••••••••••••••••••••'"
                                                        class="bg-background border-border flex-1 font-mono text-xs"
                                                    />
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        type="button"
                                                        @click="showCredentials[`edit-${editingAcquirer.id}-chave_publica`] = !showCredentials[`edit-${editingAcquirer.id}-chave_publica`]"
                                                    >
                                                        <Eye v-if="!showCredentials[`edit-${editingAcquirer.id}-chave_publica`]" class="h-4 w-4" />
                                                        <EyeOff v-else class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </div>

                                            <!-- Chave de Saque Externo (apenas LiberPay) -->
                                            <div class="space-y-2">
                                                <Label for="chave_saque_externo" class="text-sm font-medium">Chave de Saque Externo (Opcional)</Label>
                                                <div class="flex items-center gap-2">
                                                    <Input
                                                        id="chave_saque_externo"
                                                        v-model="updateForm.credentials.chave_saque_externo"
                                                        :type="showCredentials[`edit-${editingAcquirer.id}-chave_saque_externo`] ? 'text' : 'password'"
                                                        :placeholder="showCredentials[`edit-${editingAcquirer.id}-chave_saque_externo`] ? 'Cole sua chave de saque externo aqui (opcional)' : '••••••••••••••••••••••••'"
                                                        class="bg-background border-border flex-1 font-mono text-xs"
                                                    />
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        type="button"
                                                        @click="showCredentials[`edit-${editingAcquirer.id}-chave_saque_externo`] = !showCredentials[`edit-${editingAcquirer.id}-chave_saque_externo`]"
                                                    >
                                                        <Eye v-if="!showCredentials[`edit-${editingAcquirer.id}-chave_saque_externo`]" class="h-4 w-4" />
                                                        <EyeOff v-else class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </template>

                                    </div>
                                </div>
                                <DialogFooter>
                                    <Button
                                        variant="outline"
                                        @click="showCredentialsDialog = false"
                                    >
                                        Cancelar
                                    </Button>
                                    <Button
                                        @click="saveAcquirerSettings"
                                        :disabled="updateForm.processing"
                                    >
                                        <Save class="h-4 w-4 mr-2" />
                                        Salvar
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>

            <!-- Mensagem quando não há adquirentes -->
            <div v-if="acquirers.length === 0" class="text-center py-12">
                <p class="text-muted-foreground">Nenhuma adquirente configurada.</p>
            </div>
        </div>

        <Toaster />
    </AppLayout>
</template>
