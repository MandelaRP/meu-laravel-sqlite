<script setup lang="ts">
import { type BreadcrumbItem } from '@/types';
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

const { toast } = useToast()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Grupos',
        href: '/groups',
    },
];

defineProps<{
    name?: string;
}>();

const open = ref(false);

const form = useForm({
    name: '',
    description: '',
});

const submit = () => {
    form.post(route('groups.store'), {
        onSuccess: () => {
            form.reset();
            toast({
                title: 'Grupo criado com sucesso',
            });
            open.value = false;
        },
        onError: () => {
            toast({
                title: 'Erro ao criar grupo',
                variant: 'destructive',
            });
        },
    });
};

const close = () => {
    form.reset();
    open.value = false;
};
</script>

<template>
    <Toaster />

    <div>
        <Dialog :open="open" @update:open="close">

            <Button @click="open = true">Criar Grupo</Button>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Criar Grupo</DialogTitle>
                    <DialogDescription>
                        Crie um novo grupo para organizar seus sites.
                    </DialogDescription>
                </DialogHeader>
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <Input type="text" placeholder="FabWeb" v-model="form.name" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <Input type="text" placeholder="Descrição do Grupo" v-model="form.description" />
                        <InputError :message="form.errors.description" />
                    </div>
                </div>
                <DialogFooter>
                    <Button @click="submit" class="mt-2 w-full" tabindex="5" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Criar Grupo
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>

</template>
