<script setup>
import { ref, onMounted, onUnmounted, computed, defineProps, defineEmits } from 'vue';

const props = defineProps({
    checkout: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['expired']);

const timeLeft = ref(0);
const isExpired = ref(false);
const isUrgent = ref(false);
let interval = null;

// Computed properties baseadas nos dados do checkout
const enabled = computed(() => props.checkout?.countdown_enabled || false);
const icon = computed(() => props.checkout?.countdown_icon || '🔥');
const duration = computed(() => props.checkout?.countdown_duration || 3600);
const bgColor = computed(() => props.checkout?.countdown_bg_color || '#dc2626');
const textColor = computed(() => props.checkout?.countdown_text_color || '#ffffff');
const message = computed(() => props.checkout?.countdown_message || 'Oferta por tempo limitado!');
const countdownExpired = computed(() => props.checkout?.countdown_expired || false);

const bannerClasses = computed(() => {
    const baseClasses = [
        'fixed', 'top-0', 'left-0', 'right-0', 'z-50', 'w-full',
        'backdrop-blur-md', 'border-b', 'shadow-lg',
        'transform', 'transition-all', 'duration-500', 'ease-out',
        'translate-y-0', 'opacity-100'
    ];

    if (isUrgent.value && !isExpired.value) {
        baseClasses.push('');
    }

    return baseClasses.join(' ');
});

const timerClasses = computed(() => {
    const baseClasses = ['flex', 'items-center', 'gap-1', 'font-mono', 'font-bold'];
    
    if (isUrgent.value && !isExpired.value) {
        baseClasses.push('animate-bounce');
    }
    
    return baseClasses.join(' ');
});

const formatTime = (seconds) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    
    return {
        hours: hours.toString().padStart(2, '0'),
        minutes: minutes.toString().padStart(2, '0'),
        seconds: secs.toString().padStart(2, '0')
    };
};

const startCountdown = () => {
    if (!enabled.value) return;
    
    // Se já está marcado como expirado no banco, não iniciar o contador
    console.log(countdownExpired.value);
    if (countdownExpired.value) {

        console.log('expiradoooo:',countdownExpired.value);
        timeLeft.value = 0;
        isExpired.value = true;
        isUrgent.value = false;
        return;
    }
    
    // Usar a data de atualização do checkout como ponto de partida
    const startTime = new Date(props.checkout.updated_at).getTime();
    const endTime = startTime + (duration.value * 1000);
    
    const updateTimer = () => {
        const now = new Date().getTime();
        const distance = endTime - now;
        
        if (distance < 0) {
            timeLeft.value = 0;
            isExpired.value = true;
            isUrgent.value = false;
            emit('expired');
            if (interval) {
                clearInterval(interval);
            }
        } else {
            timeLeft.value = Math.floor(distance / 1000);
            isExpired.value = false;
            // Marcar como urgente quando restam menos de 5 minutos
            isUrgent.value = timeLeft.value <= 300;
        }
    };
    
    updateTimer();
    interval = setInterval(updateTimer, 1000);
};

onMounted(() => {
    if (enabled.value) {
        startCountdown();
    }
});

onUnmounted(() => {
    if (interval) {
        clearInterval(interval);
    }
});
</script>

<template>
    <Teleport to="body">
        <div v-if="enabled" :class="bannerClasses">
            <!-- Background with gradient -->
            <div class="absolute inset-0 bg-gradient-to-r opacity-95" :style="{ backgroundColor: bgColor }"></div>
            
            <!-- Content -->
            <div class="relative z-10 py-2 px-3 sm:py-3 sm:px-4">
                <div class="container mx-auto max-w-6xl">
                    <!-- Mobile Layout - Compact -->
                    <div class="block sm:hidden">
                        <div class="flex items-center justify-center mb-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-base flex-shrink-0">{{ icon }}</span>
                                <span class="text-xs font-medium truncate" :style="{ color: textColor }">{{ message }}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-center gap-2">
                            <div v-if="!isExpired" :class="timerClasses" :style="{ color: textColor }" class="flex-shrink-0">
                                <div class="bg-black/20 rounded px-1.5 py-0.5 backdrop-blur-sm">
                                    <span class="text-sm font-bold">{{ formatTime(timeLeft).hours }}</span>
                                    <span class="text-xs ml-0.5">H</span>
                                </div>
                                <span class="text-white/60 text-xs">:</span>
                                <div class="bg-black/20 rounded px-1.5 py-0.5 backdrop-blur-sm">
                                    <span class="text-sm font-bold">{{ formatTime(timeLeft).minutes }}</span>
                                    <span class="text-xs ml-0.5">M</span>
                                </div>
                                <span class="text-white/60 text-xs">:</span>
                                <div class="bg-black/20 rounded px-1.5 py-0.5 backdrop-blur-sm">
                                    <span class="text-sm font-bold">{{ formatTime(timeLeft).seconds }}</span>
                                    <span class="text-xs ml-0.5">S</span>
                                </div>
                            </div>
                            <div v-else class="flex items-center font-bold text-yellow-200 text-xs">
                                <span class="mr-1">⚠️</span>
                                <span>EXPIROU!</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Desktop Layout -->
                    <div class="hidden sm:flex items-center justify-center">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <span class="text-2xl">{{ icon }}</span>
                                    <div v-if="isUrgent && !isExpired" class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-400 rounded-full animate-ping"></div>
                                </div>
                                <span class="text-lg font-semibold" :style="{ color: textColor }">{{ message }}</span>
                            </div>
                            
                            <div class="h-6 w-px bg-white/30"></div>
                            
                            <div v-if="!isExpired" :class="timerClasses" :style="{ color: textColor }">
                                <div class="bg-black/20 rounded-lg px-3 py-2 backdrop-blur-sm border border-white/20">
                                    <span class="text-2xl font-mono">{{ formatTime(timeLeft).hours }}</span>
                                    <span class="text-sm ml-1 opacity-80 uppercase">Horas</span>
                                </div>
                                <span class="text-white/60 text-xl mx-1">:</span>
                                <div class="bg-black/20 rounded-lg px-3 py-2 backdrop-blur-sm border border-white/20">
                                    <span class="text-2xl font-mono">{{ formatTime(timeLeft).minutes }}</span>
                                    <span class="text-sm ml-1 opacity-80 uppercase">Min</span>
                                </div>
                                <span class="text-white/60 text-xl mx-1">:</span>
                                <div class="bg-black/20 rounded-lg px-3 py-2 backdrop-blur-sm border border-white/20">
                                    <span class="text-2xl font-mono">{{ formatTime(timeLeft).seconds }}</span>
                                    <span class="text-sm ml-1 opacity-80 uppercase">Seg</span>
                                </div>
                            </div>
                            <div v-else class="flex items-center font-bold text-yellow-200 text-lg">
                                <span class="mr-2 animate-bounce">⚠️</span>
                                <span>TEMPO ESGOTADO!</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
    }
    50% {
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.8);
    }
}

.-glow {
    animation: pulse-glow 2s ease-in-out infinite;
}
</style>