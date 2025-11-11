<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import { ref } from 'vue';
import 'vue3-toastify/dist/index.css';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Plus, Copy, ShoppingCart, ExternalLink, Edit, Trash2, AlertTriangle } from 'lucide-vue-next';


const breadcrumbs = [

];

defineProps({
    products: Array,
    checkouts: Array,
});

const checkoutToDelete = ref(null);
const showDeleteDialog = ref(false);

const copyCheckoutUrl = (checkout) => {
    const url = `${typeof window !== 'undefined' ? window.location.origin : ''}/checkout/${checkout.id}`;
    if (typeof navigator !== 'undefined' && navigator.clipboard) {
        navigator.clipboard.writeText(url);
        toast.success('URL copiada para a área de transferência!');
    }
};

const openCheckout = (checkout) => {
    const url = `/checkout/${checkout.id}`;
    window.open(url, '_blank');
};

const confirmDelete = (checkout) => {
    checkoutToDelete.value = checkout;
    showDeleteDialog.value = true;
};

const deleteCheckout = () => {
    if (!checkoutToDelete.value) return;

    useForm().delete(route('checkout.destroy', checkoutToDelete.value.id), {
        onSuccess: () => {
            toast.success('Checkout excluído com sucesso!');
            showDeleteDialog.value = false;
            checkoutToDelete.value = null;
        },
        onError: () => {
            toast.error('Erro ao excluir checkout');
        },
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(price);
};
</script>

<template>
    <Head title="Checkout" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-3 md:gap-6 md:p-4 lg:p-6 w-full max-w-7xl mx-auto">
            <!-- Alerta quando não há produtos -->
            <Alert v-if="!products || products.length === 0" variant="destructive">
                <AlertTriangle class="h-4 w-4" />
                <AlertDescription class="text-sm">
                    Para criar checkouts, você precisa primeiro cadastrar produtos. 
                    <Button 
                        variant="link" 
                        class="p-0 h-auto underline text-sm"
                        @click="router.visit('/seller/products/create')"
                    >
                        Clique aqui para criar seu primeiro produto
                    </Button>
                </AlertDescription>
            </Alert>

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 md:gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl lg:text-3xl font-bold tracking-tight">Checkout</h1>
                    <p class="text-muted-foreground text-xs md:text-sm mt-1">
                        Gerencie seus checkouts e links de pagamento
                    </p>
                </div>
                
                <Button 
                    @click="router.visit('/seller/checkout/create')" 
                    class="self-start sm:self-auto w-full sm:w-auto"
                    :disabled="!products || products.length === 0"
                    :title="!products || products.length === 0 ? 'Crie produtos primeiro para poder criar checkouts' : ''"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    <span class="hidden sm:inline">Novo Checkout</span>
                    <span class="sm:hidden">Novo</span>
                </Button>
            </div>

            <!-- Checkouts List -->
            <div class="space-y-3 md:space-y-4">
                <div v-if="checkouts.length === 0" class="text-center py-6 md:py-8 border border-dashed rounded-lg">
                    <ShoppingCart class="h-8 w-8 md:h-10 md:w-10 mx-auto text-muted-foreground" />
                    <h3 class="mt-3 md:mt-4 text-base md:text-lg font-medium">
                        {{ !products || products.length === 0 ? 'Nenhum produto cadastrado' : 'Nenhum checkout criado' }}
                    </h3>
                    <p class="mt-1 text-xs md:text-sm text-muted-foreground max-w-md mx-auto px-4">
                        {{ !products || products.length === 0 
                            ? 'Cadastre produtos primeiro para poder criar checkouts' 
                            : 'Crie seu primeiro checkout para começar a receber pagamentos' 
                        }}
                    </p>
                    <Button 
                        v-if="products && products.length > 0"
                        variant="outline" 
                        class="mt-3 md:mt-4 w-full sm:w-auto"
                        @click="router.visit('/seller/checkout/create')"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Criar Checkout
                    </Button>
                    <Button 
                        v-else
                        variant="outline" 
                        class="mt-3 md:mt-4 w-full sm:w-auto"
                        @click="router.visit('/seller/products/create')"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Criar Produto
                    </Button>
                </div>

                <div v-else class="grid gap-3 md:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="checkout in checkouts" :key="checkout.id" class="overflow-hidden">
                        <!-- Banner Preview -->
                        <div v-if="checkout.banner" class="w-full h-20 md:h-24 overflow-hidden">
                            <img 
                                :src="`/storage/${checkout.banner}`" 
                                :alt="`Banner do checkout ${checkout.product.name}`"
                                class="w-full h-full object-cover"
                            />
                        </div>
                        
                        <CardHeader class="pb-2 px-3 md:px-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <CardTitle class="text-base md:text-lg truncate">{{ checkout.product.name }}</CardTitle>
                                </div>
                            </div>
                        </CardHeader>
                        
                        <CardContent class="space-y-3 md:space-y-4 px-3 md:px-6">
                            <div class="flex items-center justify-between">
                                <span class="text-xs md:text-sm text-muted-foreground">Preço:</span>
                                <span class="font-medium text-sm md:text-base">{{ formatPrice(checkout.product.price) }}</span>
                            </div>
                            
                            <div class="grid grid-cols-1 gap-2">
                                <div class="flex items-center gap-1.5">
                                    <Badge variant="outline" class="text-xs">
                                        {{ checkout.layout === 'single' ? 'Uma coluna' : 'Duas colunas' }}
                                    </Badge>
                                </div>
                            </div>
                            
                            <div v-if="checkout.countdown_enabled" class="flex items-center justify-between">
                                <span class="text-xs md:text-sm text-muted-foreground">Contador:</span>
                                <Badge :variant="checkout.countdown_expired ? 'destructive' : 'default'" class="text-xs">
                                    {{ checkout.countdown_expired ? 'Expirado' : 'Ativo' }}
                                </Badge>
                            </div>

                            <Separator />
                            
                            <div class="flex gap-2">
                                <Button 
                                    variant="outline" 
                                    size="sm"
                                    @click="copyCheckoutUrl(checkout)"
                                    class="flex-1 text-xs"
                                >
                                    <Copy class="mr-1 md:mr-2 h-3 w-3 md:h-4 md:w-4" />
                                    <span class="hidden sm:inline">Copiar URL</span>
                                    <span class="sm:hidden">Copiar</span>
                                </Button>
                                <Button 
                                    variant="outline" 
                                    size="sm"
                                    @click="openCheckout(checkout)"
                                    title="Abrir checkout"
                                    class="px-2 md:px-3"
                                >
                                    <ExternalLink class="h-3 w-3 md:h-4 md:w-4" />
                                </Button>
                            </div>

                            <div class="flex gap-2">
                                <Button 
                                    variant="secondary" 
                                    size="sm"
                                    @click="router.visit(route('checkout.edit', checkout.id))"
                                    class="flex-1 text-xs"
                                >
                                    <Edit class="mr-1 md:mr-2 h-3 w-3 md:h-4 md:w-4" />
                                    <span class="hidden sm:inline">Editar</span>
                                    <span class="sm:hidden">Editar</span>
                                </Button>
                                <Button 
                                    variant="outline" 
                                    size="sm"
                                    @click="confirmDelete(checkout)"
                                    class="text-destructive hover:text-destructive px-2 md:px-3"
                                >
                                    <Trash2 class="h-3 w-3 md:h-4 md:w-4" />
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Dialog de Confirmação de Exclusão -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Confirmar Exclusão</DialogTitle>
                    <DialogDescription class="text-sm">
                        Tem certeza que deseja excluir o checkout "{{ checkoutToDelete?.product?.name }}"? 
                        Esta ação não pode ser desfeita.
                    </DialogDescription>
                </DialogHeader>
                <div class="flex flex-col sm:flex-row justify-end gap-3 mt-4">
                    <Button variant="outline" @click="showDeleteDialog = false" class="w-full sm:w-auto">
                        Cancelar
                    </Button>
                    <Button variant="destructive" @click="deleteCheckout" class="w-full sm:w-auto">
                        Excluir Checkout
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
