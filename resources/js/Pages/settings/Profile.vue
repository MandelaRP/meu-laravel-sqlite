<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { TransitionRoot } from '@headlessui/vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { useInitials } from '@/composables/useInitials';
import { maskToCPF, maskToPhone } from '@/lib/masks';
import { Edit, Camera } from 'lucide-vue-next';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    className?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Meu perfil',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const { getInitials } = useInitials();

const isEditing = ref(false);
const avatarFile = ref<File | null>(null);
const avatarPreview = ref<string | null>(user.avatar || null);

const form = useForm({
    name: user.name,
    full_name: user.full_name || '',
    document: user.document || '',
    phone: user.phone || '',
    avatar: null as File | null,
});

// Observar mudanças no user para atualizar o preview
watch(() => page.props.auth.user, (newUser) => {
    if (newUser && newUser.avatar) {
        avatarPreview.value = newUser.avatar;
    }
}, { deep: true });

const formatDocument = (doc: string | undefined) => {
    if (!doc) return '';
    const cleaned = doc.replace(/\D/g, '');
    if (cleaned.length === 11) {
        return maskToCPF(doc);
    }
    if (cleaned.length === 14) {
        // CNPJ format
        return doc.replace(/\D/g, '').replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    }
    return doc;
};

const formatPhone = (phone: string | undefined) => {
    if (!phone) return '';
    return maskToPhone(phone);
};

const getPersonTypeLabel = (type: string | undefined) => {
    if (type === 'pf') return 'Pessoa Física';
    if (type === 'pj') return 'Pessoa Jurídica';
    return type || 'Não informado';
};

const toggleEdit = () => {
    isEditing.value = !isEditing.value;
    if (!isEditing.value) {
        // Reset form when canceling
        form.name = user.name;
        form.full_name = user.full_name || '';
        form.document = user.document || '';
        form.phone = user.phone || '';
        form.avatar = null;
        form.clearErrors();
        avatarFile.value = null;
        avatarPreview.value = user.avatar || null;
    }
};

const handleAvatarChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (!file) return;
    
    // Validar tamanho
    if (file.size > 2 * 1024 * 1024) {
        alert('Arquivo muito grande. Tamanho máximo: 2MB');
        target.value = '';
        return;
    }
    
    // Validar tipo
    const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        alert('Tipo de arquivo inválido. Use PNG, JPG ou GIF');
        target.value = '';
        return;
    }
    
    avatarFile.value = file;
    form.avatar = file;
    
    // Criar preview
    const reader = new FileReader();
    reader.onload = (e) => {
        avatarPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
};

const triggerAvatarUpload = () => {
    if (isEditing.value) {
        const input = document.getElementById('avatar-upload') as HTMLInputElement;
        if (input) {
            input.click();
        }
    }
};

const submit = () => {
    // Verificar se há arquivo para enviar
    const hasFile = form.avatar instanceof File;
    
    // Se há arquivo, usar POST com forceFormData
    // O Inertia.js automaticamente detecta arquivos e usa FormData
    if (hasFile) {
        form.post(route('profile.update'), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                isEditing.value = false;
                avatarFile.value = null;
                avatarPreview.value = null;
                // Recarregar a página para atualizar o avatar na topbar
                window.location.reload();
            },
            onError: (errors) => {
                console.error('Erro ao salvar:', errors);
                // Mostrar erros de validação
                if (errors && typeof errors === 'object') {
                    const errorMessages: string[] = [];
                    Object.keys(errors).forEach((key) => {
                        if (errors[key] && Array.isArray(errors[key])) {
                            errorMessages.push(`${key}: ${errors[key][0]}`);
                        } else if (typeof errors[key] === 'string') {
                            errorMessages.push(`${key}: ${errors[key]}`);
                        }
                    });
                    if (errorMessages.length > 0) {
                        alert('Erro ao salvar:\n' + errorMessages.join('\n'));
                    } else {
                        alert('Erro ao salvar. Verifique o console para mais detalhes.');
                    }
                }
            },
        });
    } else {
        // Se não há arquivo, usar PATCH normalmente
        form.patch(route('profile.update'), {
            preserveScroll: true,
            onSuccess: () => {
                isEditing.value = false;
                avatarFile.value = null;
            },
            onError: (errors) => {
                console.error('Erro ao salvar:', errors);
                if (errors && typeof errors === 'object') {
                    const errorMessages: string[] = [];
                    Object.keys(errors).forEach((key) => {
                        if (errors[key] && Array.isArray(errors[key])) {
                            errorMessages.push(`${key}: ${errors[key][0]}`);
                        } else if (typeof errors[key] === 'string') {
                            errorMessages.push(`${key}: ${errors[key]}`);
                        }
                    });
                    if (errorMessages.length > 0) {
                        alert('Erro ao salvar:\n' + errorMessages.join('\n'));
                    }
                }
            },
        });
    }
};

const avatarUrl = computed(() => {
    // Se temos preview de data URL (arquivo selecionado mas não salvo)
    if (avatarPreview.value && avatarPreview.value.startsWith('data:')) {
        return avatarPreview.value;
    }
    
    // Se temos avatar do usuário
    if (avatarPreview.value) {
        // Se já é uma URL completa
        if (avatarPreview.value.startsWith('http://') || avatarPreview.value.startsWith('https://')) {
            return avatarPreview.value;
        }
        // Se já tem /storage/
        if (avatarPreview.value.startsWith('/storage/')) {
            return avatarPreview.value;
        }
        // Se é apenas o caminho do arquivo, adicionar /storage/
        return `/storage/${avatarPreview.value}`;
    }
    
    return null;
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Meu perfil" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <!-- Header com título e botão Editar perfil -->
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-semibold tracking-tight">Meu perfil</h2>
                    <Button 
                        v-if="!isEditing" 
                        variant="outline" 
                        @click="toggleEdit"
                        class="gap-2"
                    >
                        <Edit class="h-4 w-4" />
                        Editar perfil
                    </Button>
                    <div v-else class="flex gap-2">
                        <Button variant="outline" @click="toggleEdit">Cancelar</Button>
                        <Button @click="submit" :disabled="form.processing">Salvar</Button>
                    </div>
                </div>

                <!-- Card Dados gerais -->
                <div class="rounded-lg border bg-card p-8 shadow-sm">
                    <h3 class="mb-8 text-lg font-semibold">Dados gerais</h3>
                    
                    <div class="grid gap-8 lg:grid-cols-[180px_1fr]">
                        <!-- Foto do perfil (Lado Esquerdo) -->
                        <div class="flex flex-col items-center space-y-3">
                            <div class="relative">
                                <!-- Avatar clicável quando em edição -->
                                <div 
                                    v-if="isEditing"
                                    @click="triggerAvatarUpload"
                                    class="relative cursor-pointer"
                                >
                                    <Avatar class="h-32 w-32 overflow-hidden rounded-full ring-2 ring-border transition-opacity hover:opacity-80">
                                        <AvatarImage v-if="avatarUrl" :src="avatarUrl" :alt="user.name" />
                                        <AvatarFallback class="rounded-full bg-muted text-2xl font-semibold">
                                            {{ getInitials(user.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <!-- Overlay para edição -->
                                    <div class="absolute inset-0 flex items-center justify-center rounded-full bg-black/50 transition-opacity hover:bg-black/70">
                                        <Camera class="h-8 w-8 text-white" />
                                    </div>
                                </div>
                                <!-- Avatar não editável -->
                                <div v-else>
                                    <Avatar class="h-32 w-32 overflow-hidden rounded-full ring-2 ring-border">
                                        <AvatarImage v-if="avatarUrl" :src="avatarUrl" :alt="user.name" />
                                        <AvatarFallback class="rounded-full bg-muted text-2xl font-semibold">
                                            {{ getInitials(user.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                </div>
                            </div>
                            
                            <!-- Input file hidden -->
                            <input
                                type="file"
                                accept="image/png,image/jpeg,image/jpg,image/gif"
                                @change="handleAvatarChange"
                                class="hidden"
                                id="avatar-upload"
                            />
                            
                            <!-- Especificações da imagem -->
                            <div class="text-center">
                                <p v-if="isEditing" class="text-sm font-medium text-foreground cursor-pointer hover:text-primary" @click="triggerAvatarUpload">
                                    Clique para alterar
                                </p>
                                <p v-else class="text-sm font-medium text-foreground">Foto do perfil</p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    PNG, JPG ou GIF (Max. 2MB)
                                </p>
                            </div>
                        </div>

                        <!-- Campos de informações (Lado Direito) -->
                        <div class="space-y-5 max-w-2xl">
                            <!-- Nome completo - Campo grande de largura total -->
                            <div class="grid gap-2">
                                <Label for="full_name" class="text-sm font-medium">Nome completo</Label>
                                <Input
                                    v-if="isEditing"
                                    id="full_name"
                                    v-model="form.full_name"
                                    placeholder="Nome completo"
                                    class="h-10 w-full"
                                />
                                <div v-else class="flex h-10 w-full items-center rounded-md border border-input bg-muted/50 px-3 text-sm">
                                    {{ user.full_name || 'Não informado' }}
                                </div>
                                <InputError class="mt-1" :message="form.errors.full_name" />
                            </div>

                            <!-- Tipo de Pessoa e CPF - Lado a lado (cada um metade) -->
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Tipo de Pessoa -->
                                <div class="grid gap-2">
                                    <Label for="person_type" class="text-sm font-medium">Tipo de Pessoa</Label>
                                    <div class="flex h-10 w-full items-center rounded-md border border-input bg-muted/80 px-3 text-sm text-muted-foreground opacity-60 cursor-not-allowed">
                                        {{ getPersonTypeLabel(user.person_type) }}
                                    </div>
                                </div>

                                <!-- CPF -->
                                <div class="grid gap-2">
                                    <Label for="document" class="text-sm font-medium">{{ user.person_type === 'pj' ? 'CNPJ' : 'CPF' }}</Label>
                                    <Input
                                        v-if="isEditing"
                                        id="document"
                                        v-model="form.document"
                                        :placeholder="user.person_type === 'pj' ? 'CNPJ' : 'CPF'"
                                        class="h-10 w-full"
                                    />
                                    <div v-else class="flex h-10 w-full items-center rounded-md border border-input bg-muted/50 px-3 text-sm">
                                        {{ formatDocument(user.document) || 'Não informado' }}
                                    </div>
                                    <InputError class="mt-1" :message="form.errors.document" />
                                </div>
                            </div>

                            <!-- E-mail - Campo de largura total -->
                            <div class="grid gap-2">
                                <Label for="email" class="text-sm font-medium">E-mail</Label>
                                <div class="flex h-10 w-full items-center rounded-md border border-input bg-muted/80 px-3 text-sm text-muted-foreground opacity-60 cursor-not-allowed">
                                    {{ user.email }}
                                </div>
                            </div>

                            <!-- WhatsApp - Campo de largura total -->
                            <div class="grid gap-2">
                                <Label for="phone" class="text-sm font-medium">WhatsApp</Label>
                                <Input
                                    v-if="isEditing"
                                    id="phone"
                                    v-model="form.phone"
                                    placeholder="WhatsApp"
                                    class="h-10 w-full"
                                />
                                <div v-else class="flex h-10 w-full items-center rounded-md border border-input bg-muted/50 px-3 text-sm">
                                    {{ formatPhone(user.phone) || 'Não informado' }}
                                </div>
                                <InputError class="mt-1" :message="form.errors.phone" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensagem de verificação de e-mail -->
                <div v-if="mustVerifyEmail && !user.email_verified_at && !isEditing" class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        Seu endereço de e-mail não foi verificado.
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="ml-1 font-medium underline hover:no-underline"
                        >
                            Clique aqui para reenviar o e-mail de verificação.
                        </Link>
                    </p>

                    <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                        Um novo link de verificação foi enviado para seu endereço de e-mail.
                    </div>
                </div>

                <TransitionRoot
                    :show="form.recentlySuccessful"
                    enter="transition ease-in-out"
                    enter-from="opacity-0"
                    leave="transition ease-in-out"
                    leave-to="opacity-0"
                >
                    <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">Salvo com sucesso.</p>
                    </div>
                </TransitionRoot>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
