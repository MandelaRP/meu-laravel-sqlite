<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Search, Filter, Eye, Trash2, Settings, Package } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Pagination } from '@/components/ui/pagination';

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
        title: 'Produtos',
        href: '/admin/products',
    },
];

const props = defineProps({
    products: {
        type: Object,
        default: () => ({
            data: [],
            links: {},
            meta: {}
        })
    }
});

// Dados reais do backend
const products = computed(() => props.products.data || []);

const searchTerm = ref('');
const selectedProduct = ref(null);
const isModalOpen = ref(false);

const filteredProducts = computed(() => {
    if (!searchTerm.value) return products.value;
    
    const term = searchTerm.value.toLowerCase();
    return products.value.filter(product => 
        (product.name?.toLowerCase().includes(term)) ||
        (product.description?.toLowerCase().includes(term)) ||
        (product.sellerEmail?.toLowerCase().includes(term))
    );
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getCategoryColor = (category) => {
    const colors = {
        'Produto Digital': 'bg-blue-500/20 text-blue-400 border-blue-500/30',
        'Produto Físico': 'bg-green-500/20 text-green-400 border-green-500/30',
        'Serviço': 'bg-purple-500/20 text-purple-400 border-purple-500/30',
    };
    return colors[category] || 'bg-gray-500/20 text-gray-400 border-gray-500/30';
};

const openProductDetails = (product) => {
    selectedProduct.value = product;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedProduct.value = null;
};
</script>

<template>
    <Head title="Produtos Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mt-2 sm:mt-4 flex h-full flex-1 flex-col gap-3 sm:gap-4 p-2 sm:p-4 max-w-7xl w-full mx-auto">
            <!-- Cabeçalho -->
            <div class="space-y-1">
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Bem-vindo aos Produtos, Admin</h2>
                <p class="text-sm sm:text-base text-muted-foreground">
                    Nesta sessão você consegue visualizar todos os produtos de todos os sellers do seu sistema!
                </p>
            </div>

            <!-- Container de Produtos -->
            <Card class="w-full border-border bg-card">
                <CardHeader class="pb-4">
                    <CardTitle class="text-base sm:text-lg">Produtos</CardTitle>
                    <div class="flex flex-col sm:flex-row gap-3 mt-4">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                            <Input
                                v-model="searchTerm"
                                placeholder="Buscar por nome, descrição ou vendedor"
                                class="pl-10 bg-background border-border text-foreground"
                            />
                        </div>
                        <Button variant="outline" class="border-border hover:bg-accent hover:text-accent-foreground">
                            <Filter class="h-4 w-4 mr-2" />
                            Filtros
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="bg-muted/50 border-border hover:bg-muted/50">
                                    <TableHead class="font-semibold text-muted-foreground">Produto</TableHead>
                                    <TableHead class="font-semibold text-muted-foreground">Vendedor</TableHead>
                                    <TableHead class="font-semibold text-muted-foreground">Preço</TableHead>
                                    <TableHead class="font-semibold text-muted-foreground">Criado em</TableHead>
                                    <TableHead class="font-semibold text-muted-foreground">Ações</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="product in filteredProducts"
                                    :key="product.id"
                                    class="border-border hover:bg-muted/50 transition-colors"
                                >
                                    <TableCell>
                                        <div class="flex items-center gap-3">
                                            <div v-if="product.image" class="flex h-12 w-12 items-center justify-center rounded-lg overflow-hidden flex-shrink-0">
                                                <img :src="product.image" :alt="product.name" class="h-full w-full object-cover" />
                                            </div>
                                            <div v-else class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-500 flex-shrink-0">
                                                <Package class="h-6 w-6 text-white" />
                                            </div>
                                            <div>
                                                <div class="font-medium text-foreground">{{ product.name }}</div>
                                                <div v-if="product.description" class="text-xs text-muted-foreground line-clamp-1">{{ product.description }}</div>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-foreground">{{ product.sellerEmail }}</TableCell>
                                    <TableCell class="font-semibold text-foreground">{{ formatCurrency(product.price) }}</TableCell>
                                    <TableCell class="text-muted-foreground">{{ formatDate(product.createdAt) }}</TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 hover:bg-accent hover:text-accent-foreground"
                                                @click="openProductDetails(product)"
                                            >
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 hover:bg-accent hover:text-accent-foreground"
                                            >
                                                <Settings class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 hover:bg-destructive/10 hover:text-destructive"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
            
            <!-- Paginação -->
            <div v-if="products && ((products.meta && products.meta.total > 10 && products.meta.last_page > 1) || (products.total > 10 && products.last_page > 1))" class="mt-4">
                <Pagination :pagination="products" />
            </div>

            <!-- Modal de Detalhes -->
            <Dialog :open="isModalOpen" @update:open="closeModal">
                <DialogContent class="sm:max-w-2xl bg-card border-border">
                    <DialogHeader v-if="selectedProduct">
                        <DialogTitle class="text-xl">{{ selectedProduct.name }}</DialogTitle>
                        <DialogDescription class="text-base">
                            {{ selectedProduct.sellerEmail }}
                        </DialogDescription>
                    </DialogHeader>
                    <div v-if="selectedProduct" class="space-y-6 mt-4">
                        <div v-if="selectedProduct.image" class="flex justify-center">
                            <img :src="selectedProduct.image" :alt="selectedProduct.name" class="max-w-full h-auto max-h-64 rounded-lg" />
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-muted-foreground mb-2">Descrição</h4>
                            <p class="text-foreground">{{ selectedProduct.description || 'Sem descrição' }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-muted-foreground mb-2">Data de Criação</h4>
                            <p class="text-foreground">{{ formatDate(selectedProduct.createdAt) }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-border">
                            <div class="space-y-1">
                                <p class="text-sm text-muted-foreground">Total de Vendas</p>
                                <p class="text-2xl font-bold text-foreground">{{ selectedProduct.totalSales }} vendas</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm text-muted-foreground">Faturamento Total</p>
                                <p class="text-2xl font-bold text-foreground">{{ formatCurrency(selectedProduct.totalRevenue) }}</p>
                            </div>
                            <div class="space-y-1 col-span-2">
                                <p class="text-sm text-muted-foreground">Média Diária (últimos 30 dias)</p>
                                <p class="text-2xl font-bold text-foreground">{{ formatCurrency(selectedProduct.dailyAverage) }}/dia</p>
                            </div>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="closeModal" class="border-border hover:bg-accent hover:text-accent-foreground">
                            Fechar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

