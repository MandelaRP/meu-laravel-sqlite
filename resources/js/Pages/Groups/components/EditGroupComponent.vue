<script setup>
import { Boxes, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/vue3';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'

import { Input } from '@/components/ui/input'
import { toast } from '@/components/ui/toast'
import { h, ref } from 'vue'
import InputError from '@/components/InputError.vue'
import { LoaderCircle } from 'lucide-vue-next';


const props = defineProps({
    group: {
        type: Object,
        required: true,
        default: () => ({
            name: 'Nome',
            description: 'Descrição do Grupo',
        }),
    },
});

const edit = ref(false);
const deleteDialog = ref(false);
const confirmDelete = ref(false);

const form = useForm({
    name: props.group.name,
    description: props.group.description,
});

const deleteForm = useForm({});

function onSubmit() {
    form.put(route('groups.update', props.group.id), {
        onSuccess: () => {
            toast({
                title: 'Grupo atualizado com sucesso',
            })
            closeEdit();
        },
        onError: (error) => {
            toast({
                title: 'Erro ao atualizar o grupo',
                description: error.response.data.message,
                variant: 'destructive',
            })
        }
    })
}

const closeEdit = () => {
    form.reset();
    edit.value = false;
};

const closeDelete = () => {
    confirmDelete.value = false;
    deleteDialog.value = false;
};

function deleteGroup() {
    deleteForm.delete(route('groups.destroy', props.group.id), {
        onSuccess: () => {
            toast({
                title: 'Grupo excluído com sucesso',
            });
            closeDelete();
        },
        onError: (error) => {
            toast({
                title: 'Erro ao excluir o grupo',
                description: error.response.data.message,
                variant: 'destructive',
            })
        }
    })
}
</script>

<template>
    <div class="cursor-pointer rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-2 hover:bg-sidebar-border/70 dark:hover:bg-sidebar-border/70 transition-colors">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div
                    class="flex items-center justify-center rounded-sm bg-sidebar-border/70 dark:bg-sidebar-border/70 h-10 w-10">
                    <Boxes class="h-5 w-5" />

                </div>
                <div>
                    <h3 class="text-md font-medium">{{ group.name }}</h3>
                    <p class="text-sm text-gray-500">{{ group.description }}</p>
                    <p class="text-xs text-gray-400">{{ group.sites_count || 0 }} sites</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Button @click="edit = true" :disabled="form.processing" variant="ghost">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    <Pencil class="h-4 w-4" />
                </Button>
                <Button @click="deleteDialog = true" :disabled="deleteForm.processing" variant="ghost">
                    <LoaderCircle v-if="deleteForm.processing" class="h-4 w-4 animate-spin" />
                    <Trash2 class="h-4 w-4" />
                </Button>
            </div>
        </div>
        
        <!-- Diálogo de edição -->
        <Dialog :open="edit" @update:open="closeEdit">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Editar Grupo</DialogTitle>
                    <DialogDescription>
                        Edite o grupo para organizar seus sites.
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
                    <Button @click="onSubmit" :disabled="form.processing" class="mt-2 w-full" tabindex="5">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                        Editar Grupo
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Diálogo de exclusão -->
        <Dialog :open="deleteDialog" @update:open="closeDelete">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Excluir grupo</DialogTitle>
                    <DialogDescription>
                        Tem certeza que deseja excluir o grupo <strong>{{ props.group.name }}</strong>?
                        Esta ação não pode ser desfeita.
                    </DialogDescription>
                </DialogHeader>

                <div class="py-4">
                    <div class="flex items-center space-x-2">
                        <input class="w-4 h-4 rounded-md border border-gray-300" id="confirm-delete-group"
                            v-model="confirmDelete" type="checkbox" />
                        <label for="confirm-delete-group" class="text-sm font-medium">
                            Sim, desejo excluir este grupo
                        </label>
                    </div>
                </div>

                <DialogFooter>
                    <Button @click="deleteDialog = false" variant="outline">Cancelar</Button>
                    <Button @click="deleteGroup" type="submit" variant="destructive" :disabled="!confirmDelete || deleteForm.processing">
                        <LoaderCircle v-if="deleteForm.processing" class="h-4 w-4 animate-spin mr-2" />
                        Excluir
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>