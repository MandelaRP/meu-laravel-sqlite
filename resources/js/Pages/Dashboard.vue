<script setup>
import Banner from '@/components/Banner.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { CircleDollarSign, LineChart, Activity, EyeIcon, ArrowDownCircle, ArrowUpCircle, Clock, PlusIcon, CheckIcon, Percent, Banknote, X } from 'lucide-vue-next';
import { onMounted, ref, computed, nextTick, onBeforeUnmount } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Button } from '@/components/ui/button';
import PaymentMethodsSummary from '@/pages/Shared/components/PaymentMethodsSummary.vue';

const breadcrumbs = [{
    title: 'Dashboard',
    href: '/dashboard',
}];

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            saldoDisponivel: 0,
            faturamento: 0,
            quantidade: 0,
            ticketMedio: 0,
        })
    },
    chartData: {
        type: Array,
        default: () => []
    },
    paymentMethods: {
        type: Object,
        default: () => ({
            pix: 0,
            card: 0,
            boleto: 0,
        })
    },
    financialSummary: {
        type: Object,
        default: () => ({
            conversao: 0,
            reembolsos: 0,
            preChargeback: 0,
            chargeback: 0,
        })
    },
    transacoesRecentes: {
        type: Array,
        default: () => []
    },
    bannerUrl: {
        type: String,
        default: null
    }
});

const showBanner = ref(true);
const isChartMounted = ref(false);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0);
};

const saldoDisponivel = computed(() => formatCurrency(props.stats.saldoDisponivel));
const faturamento = computed(() => formatCurrency(props.stats.faturamento));
const quantidade = computed(() => props.stats.quantidade.toString());
const ticketMedio = computed(() => formatCurrency(props.stats.ticketMedio));

const isDarkMode = ref(false);

// Usar dados reais do backend
const dadosUltimos15Dias = computed(() => {
    if (props.chartData && props.chartData.length > 0) {
        return props.chartData.map(item => ({
            name: item.date,
            Faturamento: item.Faturamento || item.Entradas || 0 // Compatibilidade com dados antigos
        }));
    }
    
    // Fallback: gerar dados vazios se não houver dados
    const dados = [];
    const hoje = new Date();
    
    for (let i = 14; i >= 0; i--) {
        const data = new Date(hoje);
        data.setDate(data.getDate() - i);
        
        const dia = String(data.getDate()).padStart(2, '0');
        const mes = String(data.getMonth() + 1).padStart(2, '0');
        
        dados.push({
            name: `${dia}/${mes}`,
            Faturamento: 0
        });
    }
    
    return dados;
});

// Detectar tema dark/light
const checkDarkMode = () => {
    isDarkMode.value = document.documentElement.classList.contains('dark');
};

// Observar mudanças no tema
const observer = new MutationObserver(checkDarkMode);

const series = computed(() => {
    return [
        {
            name: 'Faturamento',
            data: dadosUltimos15Dias.value.map(item => item.Faturamento || item.Entradas || 0) // Compatibilidade
        }
    ];
});

const chartOptions = computed(() => {
    const isDark = isDarkMode.value;
    
    return {
        chart: {
            type: 'area',
            height: 'auto',
            zoom: { enabled: false },
            toolbar: { show: false },
            background: 'transparent',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 500,
                animateGradually: {
                    enabled: true,
                    delay: 150
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 350
                }
            }
        },
        colors: ['#22c55e'],
        dataLabels: { enabled: false },
        stroke: {
            curve: 'smooth',
            width: 2,
            colors: ['#22c55e']
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: isDark ? 0.3 : 0.2,
                opacityTo: isDark ? 0.1 : 0.05,
                stops: [0, 90, 100],
                colorStops: [
                    [
                        {
                            offset: 0,
                            color: '#22c55e',
                            opacity: isDark ? 0.3 : 0.2
                        },
                        {
                            offset: 100,
                            color: '#16a34a',
                            opacity: 0
                        }
                    ]
                ]
            }
        },
        grid: {
            show: false,
            padding: {
                top: 10,
                right: 10,
                bottom: 30,
                left: 10
            }
        },
        xaxis: {
            categories: dadosUltimos15Dias.value.map(item => item.name),
            labels: {
                show: true,
                style: {
                    colors: isDark ? '#94a3b8' : '#64748b',
                    fontSize: '11px',
                    fontFamily: 'inherit'
                },
                offsetY: 0
            },
            axisBorder: {
                show: true,
                color: isDark ? '#334155' : '#e2e8f0',
                height: 1,
                offsetY: 0,
                offsetX: 0
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            min: 0,
            labels: {
                show: false
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        legend: {
            show: false
        },
        tooltip: {
            theme: isDark ? 'dark' : 'light',
            style: { fontSize: '12px' },
            y: {
                formatter: function (value) {
                    return new Intl.NumberFormat('pt-BR', {
                        style: 'currency',
                        currency: 'BRL',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(value);
                }
            }
        },
        responsive: [{
            breakpoint: 1024,
            options: {
                chart: {
                    height: 250
                }
            }
        }, {
            breakpoint: 640,
            options: {
                chart: {
                    height: 200
                }
            }
        }]
    };
});

const chartContainer = ref(null);

onMounted(async () => {
    checkDarkMode();
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
    
    const bannerState = localStorage.getItem('dashboardBannerVisible');
    showBanner.value = bannerState === null ? true : bannerState === 'true';
    
    // Aguardar múltiplos ticks e verificar se o elemento existe
    await nextTick();
    await nextTick();
    
    // Verificar se o container existe antes de montar o chart
    setTimeout(() => {
        isChartMounted.value = true;
    }, 100);
});

onBeforeUnmount(() => {
    observer.disconnect();
});

const toggleBanner = () => {
    showBanner.value = !showBanner.value;
    localStorage.setItem('dashboardBannerVisible', showBanner.value);
};

const user = usePage().props.auth.user;

// Mensagem de aprovação - mostrar apenas uma vez quando o usuário for aprovado
const showApprovalMessage = ref(false);

// Card flutuante de cadastro enviado para análise
const showPendingCard = ref(false);

onMounted(() => {
    // Verificar se o usuário foi aprovado recentemente
    // Se o status é 'active' e o usuário nunca viu a mensagem antes
    if (user.status === 'active') {
        const approvalMessageShown = localStorage.getItem(`approval_message_shown_${user.id}`);
        if (!approvalMessageShown) {
            showApprovalMessage.value = true;
        }
    }
    
    // Verificar se acabou de enviar o cadastro para análise
    if (user.status === 'pending') {
        const onboardingSubmitted = localStorage.getItem('onboarding_submitted');
        const pendingCardShown = localStorage.getItem(`pending_card_shown_${user.id}`);
        
        if (onboardingSubmitted === 'true' && !pendingCardShown) {
            showPendingCard.value = true;
        }
    }
});

const closeApprovalMessage = () => {
    showApprovalMessage.value = false;
    localStorage.setItem(`approval_message_shown_${user.id}`, 'true');
};

const closePendingCard = () => {
    showPendingCard.value = false;
    localStorage.setItem(`pending_card_shown_${user.id}`, 'true');
    localStorage.removeItem('onboarding_submitted');
};

</script>

<template>

    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mt-2 sm:mt-4 flex h-full flex-1 flex-col gap-3 sm:gap-4 p-2 sm:p-4 max-w-7xl w-full mx-auto">
            <!-- Título e Descrição (movido para cima do banner) -->
            <div class="my-4 sm:my-6 space-y-1">
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight">Dashboard</h2>
                <p class="text-sm sm:text-base text-muted-foreground/80">Visão geral da sua conta e atividades recentes.</p>
            </div>

            <!-- Banner -->
            <Transition v-if="showBanner" name="fade">
                <Banner :bannerUrl="bannerUrl" @close="toggleBanner" />
            </Transition>

            <Alert v-if="user.status === 'pending'" class="bg-yellow-500/80">
                <Clock class="h-4 w-4" />
                <AlertTitle class="font-bold">Em Análise!</AlertTitle>
                <AlertDescription>
                    Aguarde a análise do seu cadastro.
                </AlertDescription>
            </Alert>

            <Transition v-if="showApprovalMessage" name="fade">
                <Alert class="bg-green-500/80 border-green-600">
                    <CheckIcon class="h-4 w-4" />
                    <AlertTitle class="font-bold">Cadastro Aprovado!</AlertTitle>
                    <AlertDescription class="flex items-center justify-between">
                        <span>Seu cadastro foi aprovado com sucesso. Bem-vindo ao LuckPay!</span>
                        <Button 
                            @click="closeApprovalMessage" 
                            variant="ghost" 
                            size="sm" 
                            class="ml-4 h-6 w-6 p-0 hover:bg-green-600/20"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                    </AlertDescription>
                </Alert>
            </Transition>

            <!-- Card Flutuante de Cadastro Enviado para Análise -->
            <Transition v-if="showPendingCard" name="fade">
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                    <Card class="w-full max-w-md mx-4 shadow-2xl">
                        <CardHeader class="text-center pb-4">
                            <div class="mx-auto mb-4 w-16 h-16 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                                <Clock class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <CardTitle class="text-2xl">Cadastro Enviado!</CardTitle>
                            <CardDescription class="text-base mt-2">
                                Seu cadastro foi enviado para análise
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="text-center space-y-4">
                            <p class="text-sm text-muted-foreground">
                                Nossa equipe está analisando seus documentos. Você receberá uma notificação assim que seu cadastro for aprovado.
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Enquanto isso, você pode acompanhar o status na sua área de dashboard.
                            </p>
                        </CardContent>
                        <CardContent class="pt-0 flex justify-end">
                            <Button @click="closePendingCard" variant="outline" size="sm">
                                <X class="h-4 w-4 mr-2" />
                                Entendi
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </Transition>

            <!-- Botão para mostrar banner -->
            <button v-if="!showBanner" @click="toggleBanner"
                class="flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground">
                <EyeIcon class="h-4 w-4" />
                <span>Mostrar banner de indicação</span>
            </button>

            <!-- Cards de Métricas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <!-- Card Saldo Disponível -->
                <div class="flex gap-3 sm:gap-4 rounded-xl border bg-card p-4 sm:p-6">
                    <div class="flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-lg bg-[#0284c7]">
                        <CircleDollarSign class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs sm:text-sm text-muted-foreground">Saldo Disponível</span>
                        <span class="text-lg sm:text-xl font-semibold text-card-foreground">{{ saldoDisponivel }}</span>
                    </div>
                </div>

                <!-- Card Faturamento -->
                <div class="flex gap-3 sm:gap-4 rounded-xl border bg-card p-4 sm:p-6">
                    <div class="flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-lg bg-[#22c55e]">
                        <LineChart class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs sm:text-sm text-muted-foreground">Faturamento</span>
                        <span class="text-lg sm:text-xl font-semibold text-card-foreground">{{ faturamento }}</span>
                    </div>
                </div>

                <!-- Card Quantidade -->
                <div class="flex gap-3 sm:gap-4 rounded-xl border bg-card p-4 sm:p-6">
                    <div class="flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-lg bg-[#3b82f6]">
                        <Banknote class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs sm:text-sm text-muted-foreground">Quantidade</span>
                        <span class="text-lg sm:text-xl font-semibold text-card-foreground">{{ quantidade }}</span>
                    </div>
                </div>

                <!-- Card Ticket Médio -->
                <div class="flex gap-3 sm:gap-4 rounded-xl border bg-card p-4 sm:p-6">
                    <div class="flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-lg bg-[#eab308]">
                        <Percent class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs sm:text-sm text-muted-foreground">Ticket Médio</span>
                        <span class="text-lg sm:text-xl font-semibold text-card-foreground">{{ ticketMedio }}</span>
                    </div>
                </div>
            </div>

            <!-- Gráfico e Transações -->
            <div class="grid grid-cols-1 gap-3 sm:gap-4 lg:grid-cols-7">
                <!-- Gráfico -->
                <Card class="col-span-1 lg:col-span-4">
                    <CardHeader class="pb-3 sm:pb-4">
                        <div>
                            <CardTitle class="text-base sm:text-lg">Gráfico de Faturamento</CardTitle>
                            <p class="text-xs text-muted-foreground mt-1">Últimos 15 dias</p>
                        </div>
                    </CardHeader>
                    <CardContent class="px-3 sm:px-6">
                        <div ref="chartContainer" class="h-[200px] sm:h-[250px] lg:h-[350px] w-full">
                            <VueApexCharts
                                v-if="isChartMounted && chartContainer"
                                :key="`chart-${isDarkMode}-dashboard`"
                                type="area"
                                :options="chartOptions"
                                :series="series"
                            />
                            <div v-else class="flex items-center justify-center h-full text-muted-foreground">
                                Carregando gráfico...
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Métodos de Pagamento + Resumo Financeiro -->
                <div class="col-span-1 lg:col-span-3">
                    <PaymentMethodsSummary 
                        :transactions="transacoesRecentes" 
                        :payment-methods="paymentMethods"
                        :financial-summary="financialSummary"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Transições suaves entre estados */
.slide-fade-enter-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-fade-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-fade-enter-from {
    transform: translateY(20px);
    opacity: 0;
}

.slide-fade-leave-to {
    transform: translateY(-20px);
    opacity: 0;
}

/* Animação da barra de progresso */
.progress-bar {
    animation: progress 2s ease-in-out infinite;
}

@keyframes progress {
    0% {
        width: 0%;
    }

    50% {
        width: 70%;
    }

    100% {
        width: 100%;
    }
}

/* Animações personalizadas melhoradas */
@keyframes success-pulse {

    0%,
    100% {
        transform: scale(1);
        opacity: 1;
    }

    50% {
        transform: scale(1.05);
        opacity: 0.9;
    }
}

.success-animation {
    animation: success-pulse 2s ease-in-out infinite;
}

@keyframes checkmark {
    0% {
        transform: scale(0) rotate(45deg);
        opacity: 0;
    }

    50% {
        transform: scale(1.2) rotate(45deg);
        opacity: 1;
    }

    100% {
        transform: scale(1) rotate(45deg);
        opacity: 1;
    }
}

.checkmark-animation {
    animation: checkmark 0.6s ease-out;
}

/* Hover effects */
.qr-container:hover {
    transform: scale(1.02);
    transition: transform 0.2s ease;
}

/* Responsividade melhorada */
@media (max-width: 640px) {
    .qr-code-size {
        width: 180px;
        height: 180px;
    }
}

/* Animações para lista de transações */
.transaction-list-enter-active {
    transition: all 0.3s ease-out;
}

.transaction-list-leave-active {
    transition: all 0.2s ease-in;
}

.transaction-list-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}

.transaction-list-leave-to {
    opacity: 0;
    transform: translateX(20px);
}

.transaction-list-move {
    transition: transform 0.3s ease;
}

/* Hover effects para transações */
.group:hover .transaction-hover-effect {
    transform: translateX(4px);
    transition: transform 0.2s ease;
}

/* Animação para novos itens */
@keyframes slideInFromLeft {
    from {
        opacity: 0;
        transform: translateX(-100%);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.new-transaction {
    animation: slideInFromLeft 0.5s ease-out;
}

/* Responsividade para transações */
@media (max-width: 640px) {
    .transaction-mobile {
        flex-direction: column;
        align-items: flex-start;
        space-y: 2;
    }

    .transaction-mobile .transaction-value {
        align-self: flex-end;
        margin-top: 8px;
    }
}
</style>
