<script setup>
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
import { CreditCard, Rocket, LayoutGrid, Users, Package, ShoppingCart, Wallet, BarChart3, Settings, Image as ImageIcon, Cog, Building2, LogOut } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useColorMode } from '@vueuse/core';

const mainNavItems = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'Financeiro',
        href: route('financial.index'),
        icon: Wallet,
    },
    {
        title: 'Transações',
        href: route('transactions.index'),
        icon: CreditCard,
    },
    {
        title: 'Produtos',
        href: route('products.index'),
        icon: Package,
    },
    {
        title: 'Checkout',
        href: route('checkout.index'),
        icon: ShoppingCart,
    },  
];

// Dados do backend
const page = usePage();
const pendingWithdrawals = computed(() => {
    // Buscar do backend se disponível
    return page.props.pendingWithdrawals || 0;
});
const pendingUsers = computed(() => {
    // Buscar do backend se disponível
    return page.props.pendingUsers || 0;
});

const adminNavItems = computed(() => [
    {
        title: 'Visão Geral',
        icon: BarChart3,
        items: [
            {
                title: 'Dashboard Admin',
                href: route('admin.dashboard'),
                icon: BarChart3,
            },
            {
                title: 'Usuários',
                href: route('users.index'),
                icon: Users,
                badge: pendingUsers.value,
            },
            {
                title: 'Produtos',
                href: route('admin.products.index'),
                icon: Package,
            },
        ],
    },
    {
        title: 'Financeiro',
        icon: Wallet,
        items: [
            {
                title: 'Transações',
                href: route('admin.transactions.index'),
                icon: CreditCard,
            },
            {
                title: 'Saques',
                href: route('admin.withdrawals.index'),
                icon: Wallet,
                badge: pendingWithdrawals.value,
            },
        ],
    },
    {
        title: 'Sistema',
        icon: Cog,
        items: [
            {
                title: 'Configurações',
                href: route('admin.settings.index'),
                icon: Settings,
            },
            {
                title: 'Adquirentes',
                href: route('admin.acquirers.index'),
                icon: Building2,
            },
            {
                title: 'Imagens',
                href: route('admin.images.index'),
                icon: ImageIcon,
            },
        ],
    },
]);



const footerNavItems = [
    {
        title: 'Seja Pro!',
        href: '#',
        icon: Rocket,
    },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits',
    //     icon: BookOpen,
    // },
];

const user = usePage().props.auth.user;
const isAdmin = usePage().props.is_admin;

// Função para logout
const handleLogout = () => {
    router.post(route('logout'));
};

// Controle de modo claro/escuro
const mode = useColorMode();
const toggleTheme = () => {
    mode.value = mode.value === 'dark' ? 'light' : 'dark';
};
</script>

<template>
    <!-- Sidebar para usuários ativos ou admin -->
    <Sidebar v-if="user.status === 'active' || isAdmin" collapsible="icon" variant="floating">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" label="Dashboard" />
            <NavMain :items="adminNavItems" label="Administração" v-if="isAdmin" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <div class="px-3 py-2 border-t border-sidebar-border/70">
                <button 
                    @click="toggleTheme"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm text-sidebar-foreground/80 hover:text-sidebar-foreground hover:bg-sidebar-accent transition-colors"
                >
                    <span>{{ mode === 'dark' ? 'Modo Escuro' : 'Modo Claro' }}</span>
                </button>
            </div>
        </SidebarFooter>
    </Sidebar>

    <!-- Sidebar simplificada para usuários em análise (pending) -->
    <Sidebar v-else-if="user.status === 'pending'" collapsible="icon" variant="floating">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <div class="px-4 py-2">
                <p class="text-sm text-muted-foreground">
                    Seu cadastro está em análise. Aguarde a aprovação.
                </p>
            </div>
        </SidebarContent>

        <SidebarFooter>
            <div class="px-3 py-2 border-t border-sidebar-border/70 space-y-2">
                <button 
                    @click="toggleTheme"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm text-sidebar-foreground/80 hover:text-sidebar-foreground hover:bg-sidebar-accent transition-colors"
                >
                    <span>{{ mode === 'dark' ? 'Modo Escuro' : 'Modo Claro' }}</span>
                </button>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton @click="handleLogout" class="w-full">
                            <LogOut class="h-4 w-4" />
                            <span>Sair</span>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </div>
        </SidebarFooter>
    </Sidebar>

    <slot />
</template>
