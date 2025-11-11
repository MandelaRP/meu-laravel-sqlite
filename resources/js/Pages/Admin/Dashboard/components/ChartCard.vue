<script setup>
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { onMounted, ref, computed, nextTick, onBeforeUnmount } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    chartData: {
        type: Array,
        default: () => []
    },
    dataKey: {
        type: String,
        default: 'value'
    }
});

const isDarkMode = ref(false);
const isMounted = ref(false);
const chartContainer = ref(null);

// Usar dados do backend ou gerar dados mockados
const dadosUltimos7Dias = computed(() => {
    if (props.chartData && props.chartData.length > 0) {
        return props.chartData.map(item => ({
            name: item.date,
            value: item[props.dataKey] || 0
        }));
    }
    
    // Fallback: gerar dados mockados se não houver dados do backend
    const dados = [];
    const hoje = new Date();
    
    for (let i = 6; i >= 0; i--) {
        const data = new Date(hoje);
        data.setDate(data.getDate() - i);
        
        const dia = String(data.getDate()).padStart(2, '0');
        const mes = String(data.getMonth() + 1).padStart(2, '0');
        
        dados.push({
            name: `${dia}/${mes}`,
            value: 0
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
            name: 'Valor',
            data: dadosUltimos7Dias.value.map(item => item.value)
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
            categories: dadosUltimos7Dias.value.map(item => item.name),
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

onMounted(async () => {
    checkDarkMode();
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
    
    // Aguardar múltiplos ticks e verificar se o elemento existe
    await nextTick();
    await nextTick();
    
    // Verificar se o container existe antes de montar o chart
    if (chartContainer.value) {
        // Aguardar um pouco mais para garantir que o DOM está totalmente renderizado
        setTimeout(() => {
            isMounted.value = true;
        }, 100);
    } else {
        // Se o container não existe, tentar novamente após um delay
        setTimeout(() => {
            if (chartContainer.value) {
                isMounted.value = true;
            }
        }, 300);
    }
});

onBeforeUnmount(() => {
    observer.disconnect();
});
</script>

<template>
    <Card class="w-full border-border bg-card">
        <CardHeader class="pb-3 sm:pb-4">
            <div>
                <CardTitle class="text-base sm:text-lg">{{ title }}</CardTitle>
                <p class="text-xs text-muted-foreground mt-1">Últimos 7 dias</p>
            </div>
        </CardHeader>
        <CardContent class="px-3 sm:px-6">
            <div ref="chartContainer" class="h-[200px] sm:h-[250px] lg:h-[350px] w-full">
                <VueApexCharts
                    v-if="isMounted"
                    :key="`chart-${isDarkMode}-${title}`"
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
</template>

