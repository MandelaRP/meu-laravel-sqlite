<script setup>
import { Head } from '@inertiajs/vue3';
import SingleCheckout from './SingleCheckout.vue';
import TwoColumnCheckout from './TwoColumnCheckout.vue';
const props = defineProps({
    checkout: Object,
});
</script>

<template>
    <Head title="Checkout" />
    <div
        class="min-h-screen"
        :style="`background-color: ${checkout.background_color || '#f8fafc'} !important; color: ${checkout.text_color || '#0f172a'} !important;`"
    >
        <SingleCheckout v-if="checkout.layout === 'single'" :checkout="props.checkout" />
        <TwoColumnCheckout v-if="checkout.layout === 'two-column'" :checkout="props.checkout" />
    </div>
</template>
