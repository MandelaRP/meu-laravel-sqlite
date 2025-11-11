<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarMenuSub, SidebarMenuSubButton, SidebarMenuSubItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';

const props = defineProps<{
    items: NavItem[];
    label: string;
}>();

const page = usePage<SharedData>();
// Inicializar com um objeto vazio garantido
const openSubmenus = ref<Record<string, boolean>>({});

// Função helper para garantir que openSubmenus.value seja sempre um objeto válido
const ensureOpenSubmenus = () => {
    if (!openSubmenus.value || typeof openSubmenus.value !== 'object' || Array.isArray(openSubmenus.value)) {
        openSubmenus.value = {};
    }
    return openSubmenus.value;
};

// Carregar estado dos submenus do localStorage
const loadSubmenuState = () => {
    ensureOpenSubmenus();
    try {
        const saved = localStorage.getItem('sidebar-submenus');
        if (saved) {
            const parsed = JSON.parse(saved);
            // Garantir que openSubmenus.value seja um objeto válido
            if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                openSubmenus.value = { ...parsed };
            } else {
                openSubmenus.value = {};
            }
        } else {
            openSubmenus.value = {};
        }
    } catch (e) {
        // Ignorar erros de localStorage e garantir que seja um objeto
        openSubmenus.value = {};
        // Limpar localStorage corrompido
        try {
            localStorage.removeItem('sidebar-submenus');
        } catch (e2) {
            // Ignorar
        }
    }
};

// Salvar estado dos submenus no localStorage
const saveSubmenuState = () => {
    try {
        localStorage.setItem('sidebar-submenus', JSON.stringify(openSubmenus.value));
    } catch (e) {
        // Ignorar erros de localStorage
    }
};

// Abrir submenus automaticamente se tiver item ativo
const initializeSubmenus = () => {
    ensureOpenSubmenus();
    loadSubmenuState();
    props.items.forEach(item => {
        if (item.items && hasActiveChild(item) && item.title && typeof item.title === 'string') {
            ensureOpenSubmenus();
            if (openSubmenus.value && typeof openSubmenus.value === 'object' && !Array.isArray(openSubmenus.value)) {
                openSubmenus.value[item.title] = true;
            }
        }
    });
    saveSubmenuState();
};

const toggleSubmenu = (title: string, event?: Event) => {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    if (!title || typeof title !== 'string') return; // Proteção contra título vazio ou inválido
    
    ensureOpenSubmenus();
    
    // Garantir que openSubmenus.value existe e é um objeto
    if (!openSubmenus.value || typeof openSubmenus.value !== 'object' || Array.isArray(openSubmenus.value)) {
        openSubmenus.value = {};
    }
    
    // Verificar se o submenu já está aberto
    const currentValue = openSubmenus.value[title] ?? false;
    
    // Se o submenu já está aberto, fecha. Se está fechado, fecha todos os outros e abre este.
    if (currentValue) {
        // Fechar o submenu atual
        openSubmenus.value[title] = false;
    } else {
        // Fechar todos os outros submenus primeiro (apenas um aberto por vez)
        Object.keys(openSubmenus.value).forEach(key => {
            openSubmenus.value[key] = false;
        });
        // Abrir o submenu atual
        openSubmenus.value[title] = true;
    }
    
    saveSubmenuState();
};

const isSubmenuOpen = (title: string) => {
    if (!title) return false;
    ensureOpenSubmenus();
    return openSubmenus.value[title] ?? false;
};

const hasActiveChild = (item: NavItem): boolean => {
    if (!item.items) return false;
    return item.items.some(child => child.href === page.url || hasActiveChild(child));
};

// Fechar todos os submenus após navegação
watch(() => page.url, () => {
    ensureOpenSubmenus();
    // Fechar todos os submenus após navegação
    Object.keys(openSubmenus.value).forEach(key => {
        openSubmenus.value[key] = false;
    });
    saveSubmenuState();
}, { immediate: false });

// Inicializar submenus ao montar
onMounted(() => {
    initializeSubmenus();
});
</script>

<template>
    <SidebarGroup class="px-3 py-1">
        <SidebarGroupLabel class="text-sm font-semibold text-sidebar-foreground/70 mb-3">{{ label }}</SidebarGroupLabel>
        <SidebarMenu class="space-y-1.5">
            <SidebarMenuItem v-for="item in items" :key="item.title" class="py-0.5">
                <template v-if="item.items && item.items.length > 0">
                    <SidebarMenuButton 
                        :is-active="hasActiveChild(item)"
                        @click="(e) => item.title && typeof item.title === 'string' ? toggleSubmenu(item.title, e) : null"
                        :data-state="item.title && isSubmenuOpen(item.title) ? 'open' : 'closed'"
                        class="px-3 py-3 rounded-lg transition-all"
                    >
                        <component :is="item.icon" v-if="item.icon" class="w-5 h-5" />
                        <span class="font-medium text-base text-sidebar-foreground/90">{{ item.title }}</span>
                    </SidebarMenuButton>
                    <SidebarMenuSub v-if="isSubmenuOpen(item.title)">
                        <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                            <SidebarMenuSubButton as-child :is-active="subItem.href === page.url" class="px-3 py-2.5 rounded-md">
                                <Link 
                                    :href="subItem.href" 
                                    @click="() => {
                                        if (!item.title || typeof item.title !== 'string') return;
                                        ensureOpenSubmenus();
                                        // Fechar submenu ao clicar em item
                                        if (openSubmenus.value && typeof openSubmenus.value === 'object' && !Array.isArray(openSubmenus.value)) {
                                            openSubmenus.value[item.title] = false;
                                        }
                                        saveSubmenuState();
                                    }"
                                    class="flex items-center gap-2"
                                >
                                    <component :is="subItem.icon" v-if="subItem.icon" class="w-4 h-4" />
                                    <span class="font-medium text-sm text-sidebar-foreground/80">{{ subItem.title }}</span>
                                    <span v-if="subItem.badge && subItem.badge > 0" class="ml-auto flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[10px] font-medium text-primary-foreground">
                                        {{ subItem.badge }}
                                    </span>
                                </Link>
                            </SidebarMenuSubButton>
                        </SidebarMenuSubItem>
                    </SidebarMenuSub>
                </template>
                <template v-else>
                    <SidebarMenuButton as-child :is-active="item.href === page.url" class="px-3 py-3 rounded-lg transition-all">
                        <Link :href="item.href" class="flex items-center gap-3">
                            <component :is="item.icon" v-if="item.icon" class="w-5 h-5" />
                            <span class="font-medium text-base text-sidebar-foreground/90">{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </template>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
