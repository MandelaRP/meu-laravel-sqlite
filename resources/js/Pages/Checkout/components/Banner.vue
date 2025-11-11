<script setup>
const props = defineProps({
    banner: String,
    productName: String
})
</script>

<template>
    <!-- Banner Section -->
    <div v-if="props.banner" class="mt-5 rounded-xl overflow-hidden p-2">
        <img :src="`/storage/${props.banner}`" :alt="`Banner do checkout ${props.productName}`"
            class="w-full h-full object-cover rounded-xl" />
    </div>
</template>