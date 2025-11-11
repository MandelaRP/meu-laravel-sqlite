<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useToast } from '@/components/ui/toast/use-toast';
import Toaster from '@/components/ui/toast/Toaster.vue';
import { Image as ImageIcon, Upload, Save, X } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    bannerUrl: {
        type: String,
        default: null
    },
    logoUrl: {
        type: String,
        default: null
    }
});

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Sistema',
        href: '#',
    },
    {
        title: 'Imagens',
        href: '/admin/images',
    },
];

const { toast } = useToast();

const bannerPreview = ref(props.bannerUrl);
const logoPreview = ref(props.logoUrl);
const bannerFile = ref(null);
const logoFile = ref(null);

const form = useForm({
    banner: null,
    logo: null,
});

const handleBannerUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        bannerFile.value = file;
        form.banner = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            bannerPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleLogoUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        logoFile.value = file;
        form.logo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeBanner = () => {
    bannerPreview.value = props.bannerUrl;
    bannerFile.value = null;
    form.banner = null;
};

const removeLogo = () => {
    logoPreview.value = props.logoUrl;
    logoFile.value = null;
    form.logo = null;
};

const saveImages = () => {
    form.post(route('admin.images.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: 'Imagens salvas com sucesso!',
                description: 'Banner e logo foram atualizados no sistema.',
            });
            // Recarregar a página para atualizar as URLs
            router.reload({ only: ['bannerUrl', 'logoUrl'] });
        },
        onError: (errors) => {
            toast({
                title: 'Erro ao salvar imagens',
                description: errors.message || 'Ocorreu um erro ao salvar as imagens.',
                variant: 'destructive',
            });
        },
    });
};
</script>

<template>
    <Head title="Imagens do Sistema" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mt-2 sm:mt-4 flex h-full flex-1 flex-col gap-6 p-2 sm:p-4 w-full mx-auto">
            <!-- Cabeçalho -->
            <div class="space-y-1">
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Imagens do Sistema</h2>
                <p class="text-sm sm:text-base text-muted-foreground">
                    Gerencie as imagens exibidas na plataforma.
                </p>
            </div>

            <!-- Grid de Cards - Layout Vertical -->
            <div class="flex flex-col gap-6 max-w-4xl">
                <!-- Card - Banner Promocional -->
                <Card class="border-border bg-card rounded-2xl shadow-md">
                    <CardHeader class="pb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500">
                                <ImageIcon class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <CardTitle class="text-base sm:text-lg">Banner Promocional</CardTitle>
                                <CardDescription class="text-sm mt-1">
                                    Banner exibido na Dashboard do Seller
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="banner" class="text-sm font-medium">Upload de Imagem</Label>
                            <div class="flex items-center gap-3">
                                <Input
                                    id="banner"
                                    type="file"
                                    accept="image/jpeg,image/jpg,image/png,image/webp"
                                    class="bg-background border-border"
                                    @change="handleBannerUpload"
                                />
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Formatos aceitos: JPG, PNG, WEBP. Resolução recomendada: Desktop 1123x168px, Tablet ~680x220px, Mobile ~341x250px.
                            </p>
                        </div>
                        <div v-if="bannerPreview" class="relative">
                            <img 
                                :src="bannerPreview" 
                                alt="Preview do Banner" 
                                class="w-full h-auto rounded-lg border border-border"
                            />
                            <Button
                                variant="ghost"
                                size="sm"
                                class="absolute top-2 right-2 bg-destructive/80 hover:bg-destructive text-white"
                                @click="removeBanner"
                            >
                                <X class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card - Logo da Sidebar -->
                <Card class="border-border bg-card rounded-2xl shadow-md">
                    <CardHeader class="pb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-500">
                                <ImageIcon class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <CardTitle class="text-base sm:text-lg">Logo da Sidebar</CardTitle>
                                <CardDescription class="text-sm mt-1">
                                    Logo exibida no topo da sidebar
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="logo" class="text-sm font-medium">Upload de Logo</Label>
                            <div class="flex items-center gap-3">
                                <Input
                                    id="logo"
                                    type="file"
                                    accept="image/*"
                                    class="bg-background border-border"
                                    @change="handleLogoUpload"
                                />
                            </div>
                            <p class="text-xs text-muted-foreground">
                                A logo substituirá o texto "LuckPay" no topo da sidebar.
                            </p>
                        </div>
                        <div v-if="logoPreview" class="relative flex items-center justify-center p-4 bg-muted/50 rounded-lg border border-border">
                            <img 
                                :src="logoPreview" 
                                alt="Preview da Logo" 
                                class="max-w-full max-h-32 object-contain"
                            />
                            <Button
                                variant="ghost"
                                size="sm"
                                class="absolute top-2 right-2 bg-destructive/80 hover:bg-destructive text-white"
                                @click="removeLogo"
                            >
                                <X class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Botão de Salvar -->
            <div class="flex justify-end pt-4">
                <Button 
                    @click="saveImages"
                    class="bg-primary text-primary-foreground hover:bg-primary/90"
                >
                    <Save class="h-4 w-4 mr-2" />
                    Salvar Imagens
                </Button>
            </div>
        </div>
        <Toaster />
    </AppLayout>
</template>

