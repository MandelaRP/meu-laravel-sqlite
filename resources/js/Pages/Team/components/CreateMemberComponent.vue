<script setup lang="ts">
import { type BreadcrumbItem } from '@/types';
import Button from '@/components/ui/button/Button.vue';
import Toaster from '@/components/ui/toast/Toaster.vue'
import { useToast } from '@/components/ui/toast/use-toast'
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue'
import InputError from '@/components/InputError.vue';
import { LoaderCircle } from 'lucide-vue-next';
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
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Time',
        href: route('team.index'),
    },
];


const user = usePage().props.auth.user;


const props = defineProps<{
    name?: string;
    email?: string;
    phone?: string;
    roles?: Object;
    alertChannels?: Object;
    //alert_type?: Array<string>;
    is_active?: boolean;
}>();

const form = useForm({
    user_id: user.id,
    name: 'Jhon Doe',
    email: 'jhondoe@gmail.com',
    phone: '+55 (11) 99999-9999',
    role: 'developer',
    //alert_type: <string[]>([]),
    is_active: true,
    notifications: true,
    alert_channels: ['email', 'whatsapp'],
});

const open = ref(false);
const submit = () => {
    form.post(route('team.store'), {
        onSuccess: () => {
            form.reset();
            toast({
                title: 'Membro criado com sucesso',
            });
            open.value = false;
            form.reset();
        },
        onError: (error) => {
            console.log(error);
            toast({
                title: "Oops! Houve um erro ao adicionar o membro.",
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
    <Dialog :open="open" @update:open="close">
        <DialogTrigger as-child>
            <Button @click="open = true" variant="outline">
                Novo membro
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Adicionar membro</DialogTitle>
                <DialogDescription>
                    Adicione um novo membro ao seu time para receber as notificações.
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
                                        <!-- <SelectLabel>Função</SelectLabel> -->
                                        <SelectItem v-for="role in props.roles" :key="role" :value="role">
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
                        <div v-for="(label, channel) in props.alertChannels" :key="channel"
                            class="flex items-center space-x-2">
                            <input class="w-4 h-4 rounded-md border border-gray-300 " :id="channel"
                                v-model="form.alert_channels" :value="channel" type="checkbox" />
                            <label :for="channel"
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                {{ label }}
                            </label>
                        </div>
                        <InputError :message="form.errors.alert_channels" />
                    </div>

                </div>
            </div>
            <DialogFooter>
                <Button @click="submit" type="submit" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Adicionar
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
