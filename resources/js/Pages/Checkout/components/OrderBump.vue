<script setup>
import { ref } from 'vue';

const props = defineProps({
    checkout: {
        type: Object,
        required: true
    },
    orderBump: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['order-bump-toggle']);

// Reactive state for order bump selection
const orderBumpsChecked = ref({});
const orderBumpAnimations = ref({});

const formatPrice = (price) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(price);
};

const getOrderBumpClasses = () => {
    return {
        price: 'text-green-600',
        checkbox: 'border-gray-400'
    };
};

const toggleOrderBump = (orderBumpId) => {
    // Toggle the checked state
    orderBumpsChecked.value[orderBumpId] = !orderBumpsChecked.value[orderBumpId];
    
    // Add animation
    orderBumpAnimations.value[orderBumpId] = true;
    setTimeout(() => {
        orderBumpAnimations.value[orderBumpId] = false;
    }, 500);
    
    // Emit the change
    emit('order-bump-toggle', {
        orderBumpId,
        checked: orderBumpsChecked.value[orderBumpId],
        product: props.orderBump.product
    });
};

const handleCheckboxChange = () => {
    // This function is called when the checkbox is clicked
    // The actual toggle is handled by toggleOrderBump
};
</script>

<template>
    <div class="order-bump-card relative overflow-hidden rounded-2xl transition-all duration-300 transform hover:scale-[1.02]"
        :class="[
            orderBumpsChecked[orderBump.id] ? 'order-bump-selected' : '',
            orderBumpAnimations[orderBump.id] ? 'order-bump-animating' : ''
        ]"
        :style="{
            backgroundColor: checkout?.order_bump_bg_color || '#ffffff',
            color: checkout?.order_bump_text_color || '#0f172a',
            borderColor: checkout?.order_bump_border_color || '#fbbf24'
        }"
        @click="toggleOrderBump(orderBump.id)">
        
        <div class="p-4 sm:p-6">
            <div class="flex items-start space-x-4">
                <!-- Product Image -->
                <div class="flex-shrink-0">
                    <div
                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                        <img v-if="orderBump.product.image" :src="`/${orderBump.product.image}`"
                            :alt="orderBump.product.name"
                            class="w-full h-full object-cover" />
                        <div v-else
                            class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                            {{ orderBump.product.name.charAt(0) }}
                        </div>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-2 mb-2">
                        <h5 class="text-sm sm:text-base font-bold">
                            {{ orderBump.product.name }}
                        </h5>
                        <span class="text-orange-500 gift-emoji">🎁</span>
                    </div>
                    <p class="text-xs sm:text-sm mb-3 opacity-70">
                        {{ checkout?.order_bump_description || orderBump.product.description || 'Produto adicional recomendado' }}
                    </p>
                    <div class="flex items-center justify-between">
                        <span class="text-lg sm:text-xl font-bold price-gradient"
                            :class="getOrderBumpClasses().price">
                            + {{ formatPrice(orderBump.product.price) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action Bar -->
        <div class="border-t-2 border-dashed p-4"
            :style="{
                backgroundColor: checkout?.order_bump_cta_bg_color || '#10b981',
                color: checkout?.order_bump_cta_text_color || '#ffffff',
                borderColor: checkout?.order_bump_border_color || '#fbbf24'
            }">
            <div class="flex items-center space-x-3 cursor-pointer" @click="toggleOrderBump(orderBump.id)">
                <div class="relative">
                    <input type="checkbox" :id="`order-bump-${orderBump.id}`"
                        :checked="orderBumpsChecked[orderBump.id]" 
                        @change="handleCheckboxChange"
                        @click.stop
                        class="sr-only peer" />
                    <div
                        class="w-6 h-6 rounded-full border-2 flex items-center justify-center peer-checked:bg-white peer-checked:border-green-600 transition-all duration-200 cursor-pointer"
                        :class="getOrderBumpClasses().checkbox"
                        :style="{ borderColor: checkout?.order_bump_cta_text_color || '#ffffff' }"
                        @click="toggleOrderBump(orderBump.id)">
                        <!-- Check icon with better visibility -->
                        <svg v-if="orderBumpsChecked[orderBump.id]" 
                            class="w-4 h-4 text-green-600 check-icon-animate" 
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <span class="font-bold text-sm sm:text-base flex items-center gap-2">
                    {{ checkout?.order_bump_cta_text || 'Quero comprar também!' }}
                    <span class="text-xs" :style="{ color: checkout?.order_bump_recommended_color || '#fbbf24' }">
                        {{ checkout?.order_bump_recommended_text || '(Recomendado)' }}
                    </span>
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Order Bump specific styles */
.order-bump-card {
    border: 2px dashed;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.order-bump-card:hover {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    transform: translateY(-2px);
}

.order-bump-selected {
    border-style: solid !important;
    border-color: #10b981 !important;
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1), 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.order-bump-animating {
    animation: orderBumpPulse 0.5s ease-out;
}

@keyframes orderBumpPulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

/* Check icon animation */
@keyframes checkPop {
    0% {
        transform: scale(0) rotate(-180deg);
        opacity: 0;
    }
    50% {
        transform: scale(1.2) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
}

.check-icon-animate {
    animation: checkPop 0.4s ease-out;
}
</style> 