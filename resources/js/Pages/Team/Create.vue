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
const { toast } = useToast()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Time',
        href: '/teams',
    },
];

defineProps<{
    name?: string;
    members?: [];
}>();

</script>

<template>
    <Toaster />

    <Head title="Time" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div> -->
          
                <CreateMemberComponent />
            
        </div>
    </AppLayout>
</template>
