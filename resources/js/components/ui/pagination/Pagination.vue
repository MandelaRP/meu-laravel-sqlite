<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight, MoreHorizontal } from 'lucide-vue-next';

const props = defineProps({
    pagination: {
        type: Object,
        required: true,
        default: () => ({
            data: [],
            links: [],
            meta: {
                current_page: 1,
                last_page: 1,
                per_page: 10,
                total: 0,
                from: 0,
                to: 0,
            }
        })
    }
});

// Laravel pode retornar meta diretamente ou dentro de meta
const meta = computed(() => {
    // Inertia retorna paginação com estrutura: { data: [], links: [], meta: {} }
    if (props.pagination && props.pagination.meta) {
        return props.pagination.meta;
    }
    // Fallback: se meta não existir, usar o objeto inteiro
    return props.pagination || {};
});

const currentPage = computed(() => {
    const page = meta.value?.current_page;
    return page ? parseInt(page) : 1;
});

const lastPage = computed(() => {
    const last = meta.value?.last_page;
    if (last) {
        return parseInt(last);
    }
    // Calcular lastPage se não estiver disponível
    const tot = total.value;
    const per = perPage.value;
    if (tot > 0 && per > 0) {
        return Math.max(1, Math.ceil(tot / per));
    }
    return 1;
});

const total = computed(() => {
    const tot = meta.value?.total;
    return tot ? parseInt(tot) : 0;
});

const perPage = computed(() => {
    const per = meta.value?.per_page;
    return per ? parseInt(per) : 10;
});

const from = computed(() => {
    const fr = meta.value?.from;
    return fr ? parseInt(fr) : 0;
});

const to = computed(() => {
    const t = meta.value?.to;
    return t ? parseInt(t) : 0;
});

const links = computed(() => {
    if (props.pagination.links && Array.isArray(props.pagination.links)) {
        return props.pagination.links.filter(link => link.url !== null);
    }
    return [];
});

const goToPage = (url) => {
    if (!url) return;
    // Extrair apenas a query string da URL para preservar a rota atual
    try {
        const urlObj = new URL(url, window.location.origin);
        const params = new URLSearchParams(urlObj.search);
        const pageParam = params.get('page');
        
        // Usar router.get para manter a mesma rota e apenas mudar o parâmetro page
        router.get(window.location.pathname, {
            page: pageParam || 1
        }, {
            preserveState: true,
            preserveScroll: false, // Mudar para false para ir ao topo da página
        });
    } catch (e) {
        // Fallback: usar a URL completa
        router.visit(url, {
            preserveState: true,
            preserveScroll: false,
        });
    }
};

const getPageNumber = (url) => {
    if (!url) return null;
    const match = url.match(/[?&]page=(\d+)/);
    return match ? parseInt(match[1]) : null;
};

const visiblePages = computed(() => {
    const pages = [];
    const current = currentPage.value;
    const last = lastPage.value;
    
    if (last <= 7) {
        // Se há 7 ou menos páginas, mostrar todas
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }
    } else {
        // Sempre mostrar primeira página
        pages.push(1);
        
        if (current <= 4) {
            // Perto do início
            for (let i = 2; i <= 5; i++) {
                pages.push(i);
            }
            pages.push('...');
            pages.push(last);
        } else if (current >= last - 3) {
            // Perto do fim
            pages.push('...');
            for (let i = last - 4; i <= last; i++) {
                pages.push(i);
            }
        } else {
            // No meio
            pages.push('...');
            for (let i = current - 1; i <= current + 1; i++) {
                pages.push(i);
            }
            pages.push('...');
            pages.push(last);
        }
    }
    
    return pages;
});
</script>

<template>
    <div v-if="total > 10 && lastPage > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 px-4 py-3 w-full">
        <div class="text-sm text-muted-foreground">
            Mostrando {{ from }} até {{ to }} de {{ total }} resultados
        </div>
        
        <div class="flex items-center gap-2">
            <Button
                variant="outline"
                size="sm"
                :disabled="currentPage === 1"
                @click="goToPage(pagination.links?.find(link => {
                    const label = String(link.label || '').toLowerCase();
                    return label.includes('previous') || label.includes('anterior') || label === '«';
                })?.url || pagination.links?.[0]?.url)"
                class="border-border hover:bg-accent hover:text-accent-foreground"
            >
                <ChevronLeft class="h-4 w-4" />
                <span class="hidden sm:inline">Anterior</span>
            </Button>
            
            <div class="flex items-center gap-1">
                <template v-for="(page, index) in visiblePages" :key="index">
                    <Button
                        v-if="page !== '...'"
                        variant="outline"
                        size="sm"
                        :class="[
                            'border-border hover:bg-accent hover:text-accent-foreground min-w-[2.5rem]',
                            page === currentPage ? 'bg-primary text-primary-foreground border-primary' : ''
                        ]"
                        @click="goToPage(pagination.links?.find(link => getPageNumber(link.url) === page)?.url)"
                    >
                        {{ page }}
                    </Button>
                    <span v-else class="px-2 text-muted-foreground">
                        <MoreHorizontal class="h-4 w-4" />
                    </span>
                </template>
            </div>
            
            <Button
                variant="outline"
                size="sm"
                :disabled="currentPage === lastPage || lastPage === 0"
                @click="goToPage(pagination.links?.find(link => {
                    const label = String(link.label || '').toLowerCase();
                    return label.includes('next') || label.includes('próximo') || label === '»';
                })?.url || pagination.links?.[pagination.links?.length - 1]?.url)"
                class="border-border hover:bg-accent hover:text-accent-foreground"
            >
                <span class="hidden sm:inline">Próximo</span>
                <ChevronRight class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>

