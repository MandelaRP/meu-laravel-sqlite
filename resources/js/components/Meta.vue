<!-- eslint-disable vue/block-lang -->
<script setup>
import { Progress } from '@/components/ui/progress'
import { computed } from 'vue';

const formattedCurrentValue = computed(() => {
    // Formatar com 2 casas decimais
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(props.currentValue);
});

const formattedTargetValue = computed(() => {
    // Formatar com 2 casas decimais
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(props.targetValue);
});
const props = defineProps({
    currentValue: {
        type: Number,
        default: 0
    },
    targetValue: {
        type: Number,
        default: 0
    }
});

const progress = computed(() => {
    if (props.targetValue <= 0) return 0;
    const calculated = (props.currentValue / props.targetValue) * 100;
    return Math.min(calculated, 100); // Limitar a 100%
});
</script>

<template>
  <div class="flex flex-col w-full">
    <div class="flex items-center justify-end text-muted-foreground dark:text-white py-2 px-4 w-full rounded-t-xl">
        <div class="flex items-center gap-1 text-sm">
            🚀
            <span class="font-medium">{{ formattedCurrentValue }}</span>
            <span class="text-muted-foreground mx-1">/</span>
            <span class="text-muted-foreground">{{ formattedTargetValue }}</span>
        </div>
    </div>
    <Progress :model-value="progress" class="mx-auto h-1.5 w-[70%] transition-all duration-400 ease-in-out" :style="{ '--progress-width': `${progress}%` }" />
  </div>
</template>
