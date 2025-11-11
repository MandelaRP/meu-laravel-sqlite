<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Clock, AlertTriangle, Zap, Star, Heart, Gift, Trophy, X } from 'lucide-vue-next';

const props = defineProps({
    enabled: {
        type: Boolean,
        default: false
    },
    icon: {
        type: String,
        default: 'clock'
    },
    iconType: {
        type: String,
        default: 'icon'
    },
    duration: {
        type: Number,
        default: 3600 // 1 hora em segundos
    },
    bgColor: {
        type: String,
        default: '#dc2626'
    },
    textColor: {
        type: String,
        default: '#ffffff'
    },
    message: {
        type: String,
        default: 'Oferta por tempo limitado!'
    },
    startTime: {
        type: String,
        default: null
    },
    ctaText: {
        type: String,
        default: 'Aproveitar Agora!'
    },
    dismissible: {
        type: Boolean,
        default: true
    },
    countdownExpired: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'cta-click', 'expired']);

const timeLeft = ref(0);
const isExpired = ref(false);
const isDismissed = ref(false);
const isUrgent = ref(false);
let interval: NodeJS.Timeout | null = null;

const iconMap: Record<string, any> = {
    clock: Clock,
    alert: AlertTriangle,
    zap: Zap,
    star: Star,
    heart: Heart,
    gift: Gift,
    trophy: Trophy
};

const emojiMap: Record<string, string> = {
    clock: '⏰',
    alert: '⚠️',
    fire: '🔥',
    zap: '⚡',
    star: '⭐',
    heart: '❤️',
    gift: '🎁',
    trophy: '🏆',
    rocket: '🚀',
    diamond: '💎',
    crown: '👑',
    lightning: '⚡',
    flame: '🔥',
    sparkles: '✨',
    money: '💰',
    sale: '🏷️'
};

const IconComponent = computed(() => {
    return iconMap[props.icon] || Clock;
});

const EmojiComponent = computed(() => {
    return emojiMap[props.icon] || '⏰';
});

const bannerClasses = computed(() => {
    const baseClasses = [
        'fixed', 'top-0', 'left-0', 'right-0', 'z-50', 'w-full',
        'backdrop-blur-md', 'border-b', 'shadow-lg',
        'transform', 'transition-all', 'duration-500', 'ease-out'
    ];

    if (isDismissed.value) {
        baseClasses.push('-translate-y-full', 'opacity-0');
    } else {
        baseClasses.push('translate-y-0', 'opacity-100');
    }

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

const formatTime = (seconds: number) => {
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
    if (!props.enabled || !props.startTime) return;
    
    // Se já está marcado como expirado no banco, não iniciar o contador
    if (props.countdownExpired) {
        timeLeft.value = 0;
        isExpired.value = true;
        isUrgent.value = false;
        return;
    }
    
    const startTime = new Date(props.startTime).getTime();
    const endTime = startTime + (props.duration * 1000);
    
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

const handleDismiss = () => {
    isDismissed.value = true;
    setTimeout(() => {
        emit('close');
    }, 500);
};

const handleCtaClick = () => {
    emit('cta-click');
};

onMounted(() => {
    if (props.enabled) {
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
        <div v-if="enabled && !isDismissed" :class="bannerClasses">
            <!-- Background with gradient -->
            <div class="absolute inset-0 bg-gradient-to-r opacity-95" :style="{ backgroundColor: bgColor ? bgColor : 'bg-gradient-to-r from-red-600 via-red-500 to-orange-500' }"></div>
            
            <!-- Content -->
            <div class="relative z-10 py-2 px-3 sm:py-3 sm:px-4">
                <div class="container mx-auto max-w-6xl">
                    <!-- Mobile Layout - Compact -->
                    <div class="block sm:hidden">
                        <div class="flex items-center justify-center mb-2">
                            <div class="flex items-center gap-2  min-w-0">
                                <span v-if="iconType === 'emoji'" class="text-base flex-shrink-0">{{ EmojiComponent }}</span>
                                <IconComponent v-else class="w-4 h-4  flex-shrink-0" :style="{ color: textColor }" />
                                <span class="text-xs font-medium truncate" :style="{ color: textColor }">{{ message }}</span>
                            </div>
                            <!-- <button v-if="dismissible" @click="handleDismiss" 
                                    class="p-1 hover:bg-white/20 rounded-full transition-colors duration-200 flex-shrink-0 ml-2">
                                <X class="w-3 h-3" :style="{ color: textColor }" />
                            </button> -->
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
                                <AlertTriangle class="w-3 h-3 mr-1" />
                                <span>EXPIROU!</span>
                            </div>
                            
                            <!-- <button v-if="!isExpired" @click="handleCtaClick" 
                                    class="bg-white text-red-600 px-3 py-1.5 rounded-full font-bold text-xs
                                           hover:bg-yellow-200 hover:scale-105 transform transition-all duration-200
                                           shadow-lg hover:shadow-xl flex-shrink-0 whitespace-nowrap">
                                {{ ctaText }}
                            </button> -->
                        </div>
                    </div>
                    
                    <!-- Desktop Layout -->
                    <div class="hidden sm:flex items-center justify-center">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <span v-if="iconType === 'emoji'" class="text-2xl ">{{ EmojiComponent }}</span>
                                    <IconComponent v-else class="w-6 h-6 " :style="{ color: textColor }" />
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
                                <AlertTriangle class="w-5 h-5 mr-2 animate-bounce" />
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