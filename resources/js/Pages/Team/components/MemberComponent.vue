<script setup>
import EditMemberComponent from './EditMemberComponent.vue';
import DeleteMemberComponent from './DeleteMemberComponent.vue';

defineProps({
    member: {
        type: Object,
        required: true,
    },
    roles: {
        type: Object,
        required: true
    },
    alertChannels: {
        type: Object,
        required: true
    }
});

</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center space-x-3">
            <div class="bg-primary/10 rounded-full p-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="font-medium text-lg">{{ member.name }}</h3>
                <p v-if="member.email" class="text-sm text-gray-500 dark:text-gray-400">{{ member.email }}</p>
                <p v-if="member.role" class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ member.role }}</p>
            </div>
            <div class="flex items-center space-x-1">
                <span v-if="member.is_active" class="inline-block w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                <span v-else class="inline-block w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                
                <EditMemberComponent :member="member" :roles="roles" :alert-channels="alertChannels" />
                <DeleteMemberComponent :member="member" />
            </div>
        </div>
    </div>
</template>