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
import CreateGroupComponent from './components/CreateGroupComponent.vue';
import ListGroupComponent from './components/ListGroupComponent.vue';
const { toast } = useToast()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Grupos',
        href: '/groups',
    },
];

defineProps<{
    name?: string;
    groups?: [];
}>();

</script>

<template>
    <Toaster />

    <Head title="Grupos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">

            <!-- START Criar Grupo -->
            <div class="w-full p-3 flex items-center justify-end rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <CreateGroupComponent />
            </div>
            <!-- END Criar Grupo -->

            <!-- Lista de Grupos -->
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <ListGroupComponent :groups="groups" />
            </div>
            <!-- END Lista de Grupos -->
        </div>
    </AppLayout>
</template>
