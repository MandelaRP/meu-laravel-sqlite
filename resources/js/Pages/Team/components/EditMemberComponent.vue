<script setup lang="ts">
import { type BreadcrumbItem } from '@/types';
import Button from '@/components/ui/button/Button.vue';
import Toaster from '@/components/ui/toast/Toaster.vue'
import { useToast } from '@/components/ui/toast/use-toast'
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue'
import InputError from '@/components/InputError.vue';
import { LoaderCircle, Pencil } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'

//shadcn
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    SelectLabel,
    SelectGroup,
} from '@/components/ui/select'
import { Input } from '@/components/ui/input'
import { Checkbox } from '@/components/ui/checkbox'
import { usePage } from '@inertiajs/vue3';
const { toast } = useToast()

const user = usePage().props.auth.user;

const props = defineProps<{
    member: {
        id: number;
        user_id: number;
        name: string;
        email: string;
        phone: string;
        is_active: boolean;
        alert_channels: string;
        notifications: boolean;
        roles: any[];
    };
    roles?: Object;
    alertChannels?: Object;
}>();

const form = useForm({
    user_id: props.member.user_id,
    name: props.member.name,
    email: props.member.email,
    phone: props.member.phone,
    role: '', // Será preenchido no onMounted
    is_active: props.member.is_active,
    notifications: props.member.notifications,
    alert_channels: [] as string[],
});

// Preencher os canais de alerta e a função do membro no carregamento
onMounted(() => {
    // Converter string JSON para array
    if (props.member.alert_channels) {
        try {
            // Verificar se já é um array ou se é uma string JSON
            if (typeof props.member.alert_channels === 'string') {
                form.alert_channels = JSON.parse(props.member.alert_channels);
            } else if (Array.isArray(props.member.alert_channels)) {
                form.alert_channels = props.member.alert_channels;
            }
        } catch (error) {
            console.error('Erro ao parsear alert_channels:', error);
            // Inicializar como array vazio em caso de erro
            form.alert_channels = [];
        }
    }

    // Definir o primeiro papel se existir
    if (props.member.roles && props.member.roles.length > 0) {
        form.role = props.member.roles[0].name;
    }
});

const open = ref(false);
const submit = () => {
    form.put(route('team.update', { id: props.member.id }), {
        onSuccess: () => {
            toast({
                title: 'Membro atualizado com sucesso',
            });
            open.value = false;
        },
        onError: (error) => {
            console.log(error);
            toast({
                title: "Oops! Houve um erro ao atualizar o membro.",
                variant: 'destructive',
            });
        },
    });
};

const close = () => {
    // Reset form values from props
    form.name = props.member.name;
    form.email = props.member.email;
    form.phone = props.member.phone;
    form.is_active = props.member.is_active;
    form.notifications = props.member.notifications;
    
    // Reseta alert_channels do mesmo modo que no onMounted
    if (props.member.alert_channels) {
        try {
            if (typeof props.member.alert_channels === 'string') {
                form.alert_channels = JSON.parse(props.member.alert_channels);
            } else if (Array.isArray(props.member.alert_channels)) {
                form.alert_channels = props.member.alert_channels;
            }
        } catch (error) {
            console.error('Erro ao parsear alert_channels:', error);
            form.alert_channels = [];
        }
    }
    
    // Reseta role
    if (props.member.roles && props.member.roles.length > 0) {
        form.role = props.member.roles[0].name;
    }
    
    open.value = false;
};
</script>

<template>
    <Dialog :open="open" @update:open="close">
        <DialogTrigger as-child>
            <button @click="open = true" 
                class="rounded-full p-2 text-gray-500 hover:bg-gray-100 focus:outline-none dark:text-gray-400 dark:hover:bg-gray-700">
                <Pencil class="h-4 w-4" />
            </button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Editar membro</DialogTitle>
                <DialogDescription>
                    Editar as informações do membro da equipe.
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="space-y-4 ">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="email">Nome</Label>
                            <Input v-model="form.name" placeholder="Jhon Doe" />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div>
                            <Label for="email">Email</Label>
                            <Input v-model="form.email" placeholder="jhondoe@gmail.com" />
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="email">Telefone</Label>
                            <Input v-model="form.phone" placeholder="+55 (11) 99999-9999" />
                            <InputError :message="form.errors.phone" />
                        </div>
                        <div>
                            <Label for="role">Função</Label>
                            <Select v-model="form.role">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue :value="form.role" :placeholder="form.role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="(role, key) in props.roles" :key="key" :value="key">
                                            {{ role }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.role" />
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input class="w-4 h-4 rounded-md border border-gray-300" id="notifications"
                            v-model="form.notifications" type="checkbox" />
                        <label for="notifications"
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Ativar notificações
                        </label>
                        <InputError :message="form.errors.notifications" />
                    </div>
                    <hr>
                    <div v-show="form.notifications" class="flex flex-col space-y-2 mt-5">
                        <p
                            class="text-sm mb-2 font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Canais de notificação
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                            <div v-for="(label, channel) in props.alertChannels" :key="channel"
                                class="flex items-center space-x-2">
                                <input class="w-4 h-4 rounded-md border border-gray-300 " :id="'edit-' + channel"
                                    v-model="form.alert_channels" :value="channel" type="checkbox" />
                                <label :for="'edit-' + channel"
                                    class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    {{ label }}
                                </label>
                            </div>
                        </div>
                        <InputError :message="form.errors.alert_channels" />
                    </div>
                </div>
            </div>
            <DialogFooter>
                <Button @click="submit" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Atualizar
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
