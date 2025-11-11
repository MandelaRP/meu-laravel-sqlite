<script setup>
import Banner from './components/Banner.vue';

const props = defineProps({
    checkout: Object,
});
</script>

<template>
    <div class="max-w-6xl mx-auto">
        <!-- Banner -->
        <Banner v-if="checkout.banner" :banner="checkout.banner" :product-name="checkout.product?.name || ''" />
        
        <div>
            <h1>Two Column Checkout</h1>

            <pre>
                {{ props.checkout }}
            </pre>
        </div>
    </div>
</template>