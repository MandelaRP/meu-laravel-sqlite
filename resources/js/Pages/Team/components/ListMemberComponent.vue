<script setup>
import MemberComponent from './MemberComponent.vue';

defineProps({
    members: {
        type: Array,
        default: [],
    },
    roles: {
        type: Object,
        default: {},
    },
    alertChannels: {
        type: Object,
        default: {},
    },
});
</script>

<template>
    <div v-if="members.length > 0" class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-3 p-3">
        <MemberComponent v-for="member in members" :key="member.id" :member="member" :roles="roles" :alertChannels="alertChannels" />
    </div>
    <div v-else class="flex flex-col items-center justify-center h-full gap-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
        </svg>
        <p class="text-sm text-gray-500">Adicione um membro para começar</p>
    </div>
</template>
