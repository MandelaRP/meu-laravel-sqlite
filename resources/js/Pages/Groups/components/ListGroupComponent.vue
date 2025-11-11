<script setup>
import GroupComponent from './EditGroupComponent.vue';

defineProps({
    groups: {
        type: Array,
        default: [],
    },
});
</script>

<template>
    <div v-if="groups.length > 0" class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-3 p-3">
        <GroupComponent v-for="group in groups" :key="group.id" :group="group" />
    </div>
    <div v-else class="flex flex-col items-center justify-center h-full gap-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
        </svg>
        <p class="text-sm text-gray-500">Adicione um grupo para começar</p>
    </div>
</template>
