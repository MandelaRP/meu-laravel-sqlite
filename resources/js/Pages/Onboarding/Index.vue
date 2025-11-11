<!-- eslint-disable vue/block-lang -->
<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import OnboardingForm from '@/components/onboarding/OnboardingForm.vue';

const breadcrumbs = [
    {
        title: 'LuckyPay',
        href: route('onboarding'),
    },
];
</script>

<template>
    <div>
        <Head title="Bem-vindo ao LuckyPay" />
        <OnboardingForm />
    </div>
</template>
