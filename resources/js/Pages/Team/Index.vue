<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import Button from '@/components/ui/button/Button.vue';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';
import Toaster from '@/components/ui/toast/Toaster.vue'
import ToastAction from '@/components/ui/toast/ToastAction.vue'
import { useToast } from '@/components/ui/toast/use-toast'
import Input from '@/components/ui/input/Input.vue';
import { ref, h } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { LoaderCircle } from 'lucide-vue-next';
import CreateMemberComponent from './components/CreateMemberComponent.vue';
import ListMemberComponent from './components/ListMemberComponent.vue';
import MemberComponent from './components/MemberComponent.vue';
import { computed } from 'vue';
const { toast } = useToast()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Time',
        href: '/teams',
    },
];

const props = defineProps({
    members: {
        type: Object,
        default: () => ({}),
    },
    roles: {
        type: Object,
        default: () => ({}),
    },
    alertChannels: {
        type: Object,
        default: () => ({}),
    },
});

</script>

<template>
    <Toaster />

    <Head title="Time" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">

            <!-- START Criar Membro -->
            <div class="w-full p-3 flex items-center justify-end rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <CreateMemberComponent :roles="roles" :alertChannels="alertChannels" />
            </div>
            <!-- END Criar Membro -->

            <!-- Lista de Membros -->
             
            <ListMemberComponent :members="members" :roles="roles" :alertChannels="alertChannels" />
            
            <!-- END Lista de Membros -->
        </div>
    </AppLayout>
</template>
