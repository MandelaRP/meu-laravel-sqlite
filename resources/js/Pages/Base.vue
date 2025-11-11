<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

defineProps({
    name: String,
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 w-full max-w-7xl mx-auto">
      
        </div>
    </AppLayout>
</template>
