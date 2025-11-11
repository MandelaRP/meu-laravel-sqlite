<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import { ref, computed } from 'vue';
import 'vue3-toastify/dist/index.css';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import {
  ArrowLeft,
  Trash2,
  Loader2,
  Clock,
  CheckCircle,
  Save,
  Smartphone,
  Monitor,
  Zap,
  Star,
  Heart,
  Gift,
  Trophy,
  Rocket,
  Timer,
  Sparkles,
  Wand2,
  Layout,
  Layers,
  RotateCcw,
  Menu,
  Eye,
  Palette,
  Settings,
  X
} from 'lucide-vue-next';
import ImageUpload from '@/components/ImageUpload.vue';
import { watch } from 'vue';

const props = defineProps({
  products: Array,
});

const breadcrumbs = computed(() => [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Checkout', href: '/seller/checkout' },
  { title: 'Criar Checkout', href: '/seller/checkout/create' },
]);

const isSubmitting = ref(false);
const activeTab = ref('general');
const previewMode = ref('desktop');
const hasUnsavedChanges = ref(false);
const showMobileMenu = ref(false);
const showPreview = ref(false);

const templates = [
  {
    value: 'default',
    label: 'Padrão',
    description: 'Layout clássico e confiável',
    icon: Layout,
    color: 'bg-blue-500'
  },
  {
    value: 'minimal',
    label: 'Minimalista',
    description: 'Design limpo e focado',
    icon: Layers,
    color: 'bg-green-500'
  },
  {
    value: 'premium',
    label: 'Premium',
    description: 'Visual sofisticado e elegante',
    icon: Sparkles,
    color: 'bg-purple-500'
  },
  {
    value: 'custom',
    label: 'Personalizado',
    description: 'Totalmente customizável',
    icon: Wand2,
    color: 'bg-orange-500'
  },
];

const layouts = [
  {
    value: 'single',
    label: 'Uma coluna',
    description: 'Resumo na parte inferior',
    preview: '▢'
  },
  {
    value: 'two-column',
    label: 'Duas colunas',
    description: 'Resumo na direita',
    preview: '▢▢'
  },
];

const countdownIcons = [
  { value: 'clock', label: 'Relógio', icon: Clock },
  { value: 'fire', label: 'Fogo', icon: Zap },
  { value: 'star', label: 'Estrela', icon: Star },
  { value: 'heart', label: 'Coração', icon: Heart },
  { value: 'gift', label: 'Presente', icon: Gift },
  { value: 'trophy', label: 'Troféu', icon: Trophy },
  { value: 'rocket', label: 'Foguete', icon: Rocket },
  { value: 'timer', label: 'Timer', icon: Timer },
  { value: 'sparkles', label: 'Brilhos', icon: Sparkles },
];

const countdownDurations = [
  { value: 900, label: '15 minutos' },
  { value: 1800, label: '30 minutos' },
  { value: 3600, label: '1 hora' },
  { value: 7200, label: '2 horas' },
  { value: 14400, label: '4 horas' },
  { value: 28800, label: '8 horas' },
  { value: 86400, label: '24 horas' },
];

const form = useForm({
  product_id: '',
  checkout_template: 'default',
  layout: 'single',
  banner: null,
  countdown_enabled: false,
  countdown_icon: 'clock',
  countdown_icon_type: 'icon',
  countdown_duration: 3600,
  countdown_bg_color: '#dc2626',
  countdown_text_color: '#ffffff',
  countdown_message: 'Oferta por tempo limitado!',
  dark_mode: false,
  background_color: '#f8fafc',
  step_active_color: '#3b82f6',
  step_completed_color: '#10b981',
  step_inactive_color: '#9ca3af',
  step_text_color: '#ffffff',
          payment_icon_primary_color: '#3b82f6',
        payment_icon_secondary_color: '#1d4ed8',
        payment_icon_background_color: '#eff6ff',
        enabled_payment_methods: ['pix', 'credit_card', 'boleto'],
        pix_icon_background_color: '#10b981',
        credit_card_icon_background_color: '#3b82f6',
        boleto_icon_background_color: '#f97316',
        pix_check_icon_background_color: '#ffffff',
        credit_card_check_icon_background_color: '#ffffff',
        boleto_check_icon_background_color: '#ffffff',
  button_primary_color: '#10b981',
  button_secondary_color: '#059669',
  button_hover_primary_color: '#059669',
  button_hover_secondary_color: '#047857',
  order_bump_ids: [],
});

// Cores padrão do botão
const DEFAULT_BUTTON_COLORS = {
  primary: '#10b981',
  secondary: '#059669',
  hoverPrimary: '#059669',
  hoverSecondary: '#047857',
};

// Cores padrão dos steps
const DEFAULT_STEP_COLORS = {
  active: '#3b82f6',
  completed: '#10b981',
  inactive: '#9ca3af',
  text: '#ffffff',
};

// Cores padrão dos ícones de pagamento
const DEFAULT_PAYMENT_ICON_COLORS = {
  primary: '#3b82f6',
  secondary: '#1d4ed8',
  background: '#eff6ff',
};

// Cores padrão dos métodos de pagamento
const DEFAULT_PAYMENT_METHOD_COLORS = {
    pix: '#10b981',
    credit_card: '#3b82f6',
    boleto: '#f97316'
}

const DEFAULT_CHECK_ICON_BACKGROUND_COLORS = {
    pix: '#ffffff',
    credit_card: '#ffffff',
    boleto: '#ffffff'
};

// Temas predefinidos
const PREDEFINED_THEMES = [
  {
    id: 'default',
    name: 'Padrão',
    description: 'Tema clássico com cores azuis e verdes',
    colors: {
      background_color: '#f8fafc',
      step_active_color: '#3b82f6',
      step_completed_color: '#10b981',
      step_inactive_color: '#9ca3af',
      step_text_color: '#ffffff',
      button_primary_color: '#10b981',
      button_secondary_color: '#059669',
      button_hover_primary_color: '#059669',
      button_hover_secondary_color: '#047857',
      pix_icon_background_color: '#10b981',
      credit_card_icon_background_color: '#3b82f6',
      boleto_icon_background_color: '#f97316',
      pix_check_icon_background_color: '#ffffff',
      credit_card_check_icon_background_color: '#ffffff',
      boleto_check_icon_background_color: '#ffffff',
    }
  },
  {
    id: 'modern',
    name: 'Moderno',
    description: 'Tema elegante com gradientes roxos',
    colors: {
      background_color: '#fafafa',
      step_active_color: '#8b5cf6',
      step_completed_color: '#10b981',
      step_inactive_color: '#9ca3af',
      step_text_color: '#ffffff',
      button_primary_color: '#8b5cf6',
      button_secondary_color: '#7c3aed',
      button_hover_primary_color: '#7c3aed',
      button_hover_secondary_color: '#6d28d9',
      pix_icon_background_color: '#10b981',
      credit_card_icon_background_color: '#8b5cf6',
      boleto_icon_background_color: '#f59e0b',
      pix_check_icon_background_color: '#ffffff',
      credit_card_check_icon_background_color: '#ffffff',
      boleto_check_icon_background_color: '#ffffff',
    }
  },
  {
    id: 'warm',
    name: 'Quente',
    description: 'Tema acolhedor com tons laranjas e vermelhos',
    colors: {
      background_color: '#fef7ed',
      step_active_color: '#f97316',
      step_completed_color: '#10b981',
      step_inactive_color: '#9ca3af',
      step_text_color: '#ffffff',
      button_primary_color: '#f97316',
      button_secondary_color: '#ea580c',
      button_hover_primary_color: '#ea580c',
      button_hover_secondary_color: '#dc2626',
      pix_icon_background_color: '#10b981',
      credit_card_icon_background_color: '#f97316',
      boleto_icon_background_color: '#dc2626',
      pix_check_icon_background_color: '#ffffff',
      credit_card_check_icon_background_color: '#ffffff',
      boleto_check_icon_background_color: '#ffffff',
    }
  },
  {
    id: 'ocean',
    name: 'Oceano',
    description: 'Tema refrescante com tons azuis e turquesa',
    colors: {
      background_color: '#f0f9ff',
      step_active_color: '#06b6d4',
      step_completed_color: '#10b981',
      step_inactive_color: '#9ca3af',
      step_text_color: '#ffffff',
      button_primary_color: '#06b6d4',
      button_secondary_color: '#0891b2',
      button_hover_primary_color: '#0891b2',
      button_hover_secondary_color: '#0e7490',
      pix_icon_background_color: '#10b981',
      credit_card_icon_background_color: '#06b6d4',
      boleto_icon_background_color: '#f59e0b',
      pix_check_icon_background_color: '#ffffff',
      credit_card_check_icon_background_color: '#ffffff',
      boleto_check_icon_background_color: '#ffffff',
    }
  },
  {
    id: 'forest',
    name: 'Floresta',
    description: 'Tema natural com tons verdes e marrons',
    colors: {
      background_color: '#f7fee7',
      step_active_color: '#16a34a',
      step_completed_color: '#10b981',
      step_inactive_color: '#9ca3af',
      step_text_color: '#ffffff',
      button_primary_color: '#16a34a',
      button_secondary_color: '#15803d',
      button_hover_primary_color: '#15803d',
      button_hover_secondary_color: '#166534',
      pix_icon_background_color: '#10b981',
      credit_card_icon_background_color: '#16a34a',
      boleto_icon_background_color: '#d97706',
      pix_check_icon_background_color: '#ffffff',
      credit_card_check_icon_background_color: '#ffffff',
      boleto_check_icon_background_color: '#ffffff',
    }
  },
  {
    id: 'sunset',
    name: 'Pôr do Sol',
    description: 'Tema vibrante com gradientes rosa e laranja',
    colors: {
      background_color: '#fdf2f8',
      step_active_color: '#ec4899',
      step_completed_color: '#10b981',
      step_inactive_color: '#9ca3af',
      step_text_color: '#ffffff',
      button_primary_color: '#ec4899',
      button_secondary_color: '#db2777',
      button_hover_primary_color: '#db2777',
      button_hover_secondary_color: '#be185d',
      pix_icon_background_color: '#10b981',
      credit_card_icon_background_color: '#ec4899',
      boleto_icon_background_color: '#f59e0b',
      pix_check_icon_background_color: '#ffffff',
      credit_card_check_icon_background_color: '#ffffff',
      boleto_check_icon_background_color: '#ffffff',
    }
  },
  {
    id: 'midnight',
    name: 'Meia-Noite',
    description: 'Tema escuro elegante para modo noturno',
    colors: {
      background_color: '#1e293b',
      step_active_color: '#6366f1',
      step_completed_color: '#10b981',
      step_inactive_color: '#64748b',
      step_text_color: '#ffffff',
      button_primary_color: '#6366f1',
      button_secondary_color: '#4f46e5',
      button_hover_primary_color: '#4f46e5',
      button_hover_secondary_color: '#4338ca',
      pix_icon_background_color: '#10b981',
      credit_card_icon_background_color: '#6366f1',
      boleto_icon_background_color: '#f59e0b',
      pix_check_icon_background_color: '#ffffff',
      credit_card_check_icon_background_color: '#ffffff',
      boleto_check_icon_background_color: '#ffffff',
    }
  },
  {
    id: 'minimal',
    name: 'Minimalista',
    description: 'Tema limpo com cores neutras',
    colors: {
      background_color: '#ffffff',
      step_active_color: '#6b7280',
      step_completed_color: '#10b981',
      step_inactive_color: '#d1d5db',
      step_text_color: '#ffffff',
      button_primary_color: '#6b7280',
      button_secondary_color: '#4b5563',
      button_hover_primary_color: '#4b5563',
      button_hover_secondary_color: '#374151',
      pix_icon_background_color: '#10b981',
      credit_card_icon_background_color: '#6b7280',
      boleto_icon_background_color: '#f59e0b',
      pix_check_icon_background_color: '#ffffff',
      credit_card_check_icon_background_color: '#ffffff',
      boleto_check_icon_background_color: '#ffffff',
    }
  }
];

// Função para aplicar tema
const applyTheme = (theme) => {
  if (confirm(`Tem certeza que deseja aplicar o tema "${theme.name}"? Isso irá sobrescrever todas as cores personalizadas.`)) {
    Object.keys(theme.colors).forEach(key => {
      if (form[key] !== undefined) {
        form[key] = theme.colors[key];
      }
    });
    toast.success(`Tema "${theme.name}" aplicado com sucesso!`);
  }
};

// Função para verificar se o tema atual corresponde a algum tema predefinido
const getCurrentTheme = computed(() => {
  return PREDEFINED_THEMES.find(theme => {
    return Object.keys(theme.colors).every(key => {
      return form[key] === theme.colors[key];
    });
  });
});

const isButtonColorsDefault = computed(() => {
  return form.button_primary_color === DEFAULT_BUTTON_COLORS.primary &&
    form.button_secondary_color === DEFAULT_BUTTON_COLORS.secondary &&
    form.button_hover_primary_color === DEFAULT_BUTTON_COLORS.hoverPrimary &&
    form.button_hover_secondary_color === DEFAULT_BUTTON_COLORS.hoverSecondary;
});

// Computed to filter out the main product from order bumps
const availableProductsForOrderBumps = computed(() => {
  return props.products.filter(p => 
    p.id != form.product_id
  );
})

const resetButtonColors = () => {
  if (confirm('Tem certeza que deseja resetar as cores do botão para o padrão?\n\nCores padrão:\n• Primária: #10b981 (Verde)\n• Secundária: #059669 (Verde escuro)\n• Hover: #059669 → #047857')) {
    form.button_primary_color = DEFAULT_BUTTON_COLORS.primary;
    form.button_secondary_color = DEFAULT_BUTTON_COLORS.secondary;
    form.button_hover_primary_color = DEFAULT_BUTTON_COLORS.hoverPrimary;
    form.button_hover_secondary_color = DEFAULT_BUTTON_COLORS.hoverSecondary;
    toast.success('Cores do botão resetadas para o padrão!');
  }
};

const resetBackgroundColor = () => {
  if (confirm('Tem certeza que deseja resetar a cor de fundo para o padrão?\n\nCor padrão: #f8fafc (Cinza claro)')) {
    form.background_color = '#f8fafc';
    toast.success('Cor de fundo resetada para o padrão!');
  }
};

const resetStepColors = () => {
  if (confirm('Tem certeza que deseja resetar as cores dos steps para o padrão?\n\nCores padrão:\n• Ativo: #3b82f6 (Azul)\n• Completado: #10b981 (Verde)\n• Inativo: #9ca3af (Cinza)\n• Texto: #ffffff (Branco)')) {
    form.step_active_color = DEFAULT_STEP_COLORS.active;
    form.step_completed_color = DEFAULT_STEP_COLORS.completed;
    form.step_inactive_color = DEFAULT_STEP_COLORS.inactive;
    form.step_text_color = DEFAULT_STEP_COLORS.text;
    toast.success('Cores dos steps resetadas para o padrão!');
  }
};

const resetPaymentIconColors = () => {
  if (confirm('Tem certeza que deseja resetar as cores dos ícones de pagamento para o padrão?\n\nCores padrão:\n• Primária: #3b82f6 (Azul)\n• Secundária: #1d4ed8 (Azul escuro)\n• Fundo: #eff6ff (Azul claro)')) {
    form.payment_icon_primary_color = DEFAULT_PAYMENT_ICON_COLORS.primary;
    form.payment_icon_secondary_color = DEFAULT_PAYMENT_ICON_COLORS.secondary;
    form.payment_icon_background_color = DEFAULT_PAYMENT_ICON_COLORS.background;
    toast.success('Cores dos ícones de pagamento resetadas para o padrão!');
  }
};

const resetPaymentMethodColors = () => {
  if (confirm('Tem certeza que deseja resetar as cores dos métodos de pagamento para o padrão?\n\nCores padrão:\n• PIX: #10b981 (Verde)\n• Cartão de Crédito: #3b82f6 (Azul)\n• Boleto: #f97316 (Laranja)\n\nCores dos ícones de check:\n• PIX: #ffffff (Branco)\n• Cartão de Crédito: #ffffff (Branco)\n• Boleto: #ffffff (Branco)')) {
    form.pix_icon_background_color = DEFAULT_PAYMENT_METHOD_COLORS.pix;
    form.credit_card_icon_background_color = DEFAULT_PAYMENT_METHOD_COLORS.credit_card;
    form.boleto_icon_background_color = DEFAULT_PAYMENT_METHOD_COLORS.boleto;
    form.pix_check_icon_background_color = DEFAULT_CHECK_ICON_BACKGROUND_COLORS.pix;
    form.credit_card_check_icon_background_color = DEFAULT_CHECK_ICON_BACKGROUND_COLORS.credit_card;
    form.boleto_check_icon_background_color = DEFAULT_CHECK_ICON_BACKGROUND_COLORS.boleto;
    toast.success('Cores dos métodos de pagamento resetadas para o padrão!');
  }
};

const togglePaymentMethod = (method) => {
  if (!form.enabled_payment_methods.includes(method)) {
    form.enabled_payment_methods.push(method);
  } else {
    form.enabled_payment_methods = form.enabled_payment_methods.filter(m => m !== method);
  }
};

const isBackgroundColorDefault = computed(() => {
  return form.background_color === '#f8fafc';
});

const isStepColorsDefault = computed(() => {
  return form.step_active_color === DEFAULT_STEP_COLORS.active &&
    form.step_completed_color === DEFAULT_STEP_COLORS.completed &&
    form.step_inactive_color === DEFAULT_STEP_COLORS.inactive &&
    form.step_text_color === DEFAULT_STEP_COLORS.text;
});

const isPaymentIconColorsDefault = computed(() => {
  return form.payment_icon_primary_color === DEFAULT_PAYMENT_ICON_COLORS.primary &&
    form.payment_icon_secondary_color === DEFAULT_PAYMENT_ICON_COLORS.secondary &&
    form.payment_icon_background_color === DEFAULT_PAYMENT_ICON_COLORS.background;
});

const isPaymentMethodColorsDefault = computed(() => {
  return form.pix_icon_background_color === DEFAULT_PAYMENT_METHOD_COLORS.pix &&
    form.credit_card_icon_background_color === DEFAULT_PAYMENT_METHOD_COLORS.credit_card &&
    form.boleto_icon_background_color === DEFAULT_PAYMENT_METHOD_COLORS.boleto &&
    form.pix_check_icon_background_color === DEFAULT_CHECK_ICON_BACKGROUND_COLORS.pix &&
    form.credit_card_check_icon_background_color === DEFAULT_CHECK_ICON_BACKGROUND_COLORS.credit_card &&
    form.boleto_check_icon_background_color === DEFAULT_CHECK_ICON_BACKGROUND_COLORS.boleto;
});

const getContrastColor = (backgroundColor) => {
  // Verificar se a cor de fundo existe e é válida
  if (!backgroundColor || typeof backgroundColor !== 'string') {
    return 'text-gray-900'; // Fallback para texto escuro
  }
  
  // Converte a cor hex para RGB
  const hex = backgroundColor.replace('#', '');
  const r = parseInt(hex.substr(0, 2), 16);
  const g = parseInt(hex.substr(2, 2), 16);
  const b = parseInt(hex.substr(4, 2), 16);
  
  // Verificar se os valores RGB são válidos
  if (isNaN(r) || isNaN(g) || isNaN(b)) {
    return 'text-gray-900'; // Fallback para texto escuro
  }
  
  // Calcula a luminância
  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
  
  // Retorna branco para fundos escuros, preto para fundos claros
  return luminance > 0.5 ? 'text-gray-900' : 'text-white';
};

// Funções para gerenciar order bumps
const toggleOrderBump = (productId) => {
  const currentIds = form.order_bump_ids;
  const index = currentIds.findIndex(id => id == productId);
  
  if (index > -1) {
    // Remove o item mantendo a reatividade
    form.order_bump_ids = currentIds.filter(id => id != productId);
  } else {
    // Adiciona o item mantendo a reatividade
    form.order_bump_ids = [...currentIds, productId];
  }
};

const removeOrderBump = (productId) => {
  form.order_bump_ids = form.order_bump_ids.filter(id => id != productId);
};

// Watch for product_id changes to remove it from order bumps
watch(() => form.product_id, (newProductId) => {
  form.order_bump_ids = form.order_bump_ids.filter(
    id => id != newProductId
  );
})

const submit = () => {
  isSubmitting.value = true;

  // Limpar order_bump_ids para garantir que apenas IDs válidos sejam enviados
  const validOrderBumpIds = form.order_bump_ids.filter(id => {
    // Verificar se o ID existe nos produtos disponíveis
    const productExists = props.products.some(p => p.id == id);
    // Verificar se não é o produto principal
    const isNotMainProduct = id != form.product_id;
    
    console.log(`Validating order bump ID: ${id} - exists: ${productExists}, not main: ${isNotMainProduct}`);
    
    return productExists && isNotMainProduct;
  });
  
  // Atualizar o form com apenas os IDs válidos
  form.order_bump_ids = validOrderBumpIds;

  form.post(route('checkout.store'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Checkout criado com sucesso!');
      hasUnsavedChanges.value = false;
    },
    onError: () => {
      toast.error('Erro ao criar checkout');
    },
    onFinish: () => {
      isSubmitting.value = false;
    },
  });
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(price);
};

// Função para alternar preview
const togglePreview = () => {
  showPreview.value = !showPreview.value;
};

// Função para alternar modo de preview
const switchPreviewMode = (mode) => {
  previewMode.value = mode;
};
</script>

<template>
  <Head title="Criar Checkout" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 p-3 sm:p-6 w-full max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div class="flex items-center justify-between sm:justify-start">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Criar Checkout</h1>
            <p class="text-muted-foreground text-sm mt-1">
              Configure seu novo checkout de pagamento
            </p>
          </div>

          <!-- Mobile Menu Button -->
          <Button variant="outline" size="sm" class="sm:hidden" @click="showMobileMenu = !showMobileMenu">
            <Menu class="h-4 w-4" />
          </Button>
        </div>

        <!-- Desktop Actions -->
        <div class="hidden sm:flex items-center gap-3">
          <Button variant="outline" @click="router.visit('/seller/checkout')">
            <ArrowLeft class="mr-2 h-4 w-4" />
            Voltar
          </Button>
          <Button variant="outline" @click="togglePreview">
            <Eye class="mr-2 h-4 w-4" />
            Preview
          </Button>
          <Button @click="submit" :disabled="isSubmitting || !form.product_id">
            <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
            <Save v-else class="mr-2 h-4 w-4" />
            {{ isSubmitting ? 'Criando...' : 'Criar Checkout' }}
          </Button>
        </div>
      </div>

      <!-- Mobile Actions -->
      <div v-if="showMobileMenu" class="sm:hidden space-y-2 p-4 bg-gray-50 rounded-lg border mobile-menu">
        <div class="flex items-center justify-between mb-3">
          <span class="text-sm font-medium text-gray-700">Ações</span>
          <Button variant="ghost" size="sm" @click="showMobileMenu = false">
            <X class="h-4 w-4" />
          </Button>
        </div>
        <Button variant="outline" @click="router.visit('/seller/checkout')" class="w-full btn-mobile">
          <ArrowLeft class="mr-2 h-4 w-4" />
          Voltar
        </Button>
        <Button variant="outline" @click="togglePreview" class="w-full btn-mobile">
          <Eye class="mr-2 h-4 w-4" />
          Preview
        </Button>
        <Button @click="submit" :disabled="isSubmitting || !form.product_id" class="w-full btn-mobile">
          <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
          <Save v-else class="mr-2 h-4 w-4" />
          {{ isSubmitting ? 'Criando...' : 'Criar Checkout' }}
        </Button>
      </div>

      <!-- Content -->
      <div class="grid gap-4 sm:gap-6" :class="showPreview ? 'lg:grid-cols-2' : 'lg:grid-cols-1'">
        <!-- Form -->
        <div class="lg:col-span-1">
          <Card>
            <CardContent class="p-3 sm:p-6">
              <Tabs v-model="activeTab" class="w-full">
                <TabsList class="grid w-full grid-cols-3 sm:grid-cols-3 mb-4 sm:mb-6">
                  <TabsTrigger value="general" class="text-xs sm:text-sm flex items-center gap-1 btn-mobile">
                    <Settings class="h-3 w-3 sm:h-4 sm:w-4" />
                    <span class="hidden sm:inline">Geral</span>
                  </TabsTrigger>
                  <TabsTrigger value="design" class="text-xs sm:text-sm flex items-center gap-1 btn-mobile">
                    <Palette class="h-3 w-3 sm:h-4 sm:w-4" />
                    <span class="hidden sm:inline">Design</span>
                  </TabsTrigger>
                  <TabsTrigger value="countdown" class="text-xs sm:text-sm flex items-center gap-1 btn-mobile">
                    <Timer class="h-3 w-3 sm:h-4 sm:w-4" />
                    <span class="hidden sm:inline">Contador</span>
                  </TabsTrigger>

                </TabsList>

                <!-- General Tab -->
                <TabsContent value="general" class="space-y-6 sm:space-y-8">
                  <!-- Product Selection -->
                  <div class="space-y-3 sm:space-y-4">
                    <Label for="product" class="text-base font-semibold">Produto Principal</Label>
                    <p class="text-sm text-muted-foreground">
                      Selecione o produto que será vendido neste checkout
                    </p>
                    <div class="grid gap-3 sm:gap-4">
                      <div v-for="product in props.products" :key="product.id" @click="form.product_id = product.id"
                        class="relative p-3 sm:p-4 border-2 rounded-xl cursor-pointer transition-all hover:shadow-md touch-manipulation active:scale-95"
                        :class="form.product_id === product.id
                          ? 'border-blue-500 bg-blue-50 dark:bg-blue-950'
                          : 'border-slate-200 hover:border-slate-300'">
                        <div class="flex items-start gap-3 sm:gap-4">
                          <img :src="`/${product.image}`" :alt="product.name"
                            class="w-12 h-12 sm:w-16 sm:h-16 rounded-lg object-cover border flex-shrink-0" />
                          <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-sm sm:text-base truncate">{{ product.name }}</h4>
                            <p class="text-xs sm:text-sm text-muted-foreground mb-2 line-clamp-2">{{ product.description
                              }}</p>
                            <div class="text-base sm:text-lg font-bold text-green-600">{{ formatPrice(product.price) }}
                            </div>
                          </div>
                        </div>
                        <CheckCircle v-if="form.product_id === product.id"
                          class="absolute top-3 right-3 sm:top-4 sm:right-4 h-4 w-4 sm:h-5 sm:w-5 text-blue-500" />
                      </div>
                    </div>
                  </div>

                  <!-- Order Bumps Selection -->
                  <div v-if="props.products.length > 1" class="space-y-3 sm:space-y-4">
                    <Label for="order_bumps" class="text-base font-semibold">Order Bumps</Label>
                    <p class="text-sm text-muted-foreground">
                      Adicione produtos extras que serão oferecidos durante o checkout para aumentar o valor da compra
                    </p>
                    <p v-if="form.product_id && form.product_id !== ''" class="text-xs text-muted-foreground bg-muted p-2 rounded">
                      <CheckCircle class="inline h-3 w-3 mr-1" />
                      O produto principal selecionado não aparece nesta lista para evitar duplicação
                    </p>
                    <p class="text-xs text-muted-foreground bg-blue-50 p-2 rounded border border-blue-200">
                      <AlertCircle class="inline h-3 w-3 mr-1" />
                      <strong>Dica:</strong> Se você selecionar um produto como order bump e depois escolhê-lo como produto principal, ele será automaticamente removido dos order bumps.
                    </p>


                    <!-- Lista de produtos disponíveis -->
                    <div class="max-h-[400px] sm:max-h-[500px] overflow-y-auto border rounded-xl p-3 space-y-3 bg-white shadow-sm">
                      <div v-for="product in availableProductsForOrderBumps" :key="product.id"
                        class="relative flex items-center p-3 rounded-lg cursor-pointer transition-all hover:bg-slate-50 touch-manipulation active:scale-[0.98] border"
                        :class="form.order_bump_ids.includes(product.id) ? 'border-blue-500 bg-blue-50/50' : 'border-slate-200'"
                        @click="toggleOrderBump(product.id)">
                        
                        <div class="absolute left-3 top-1/2 -translate-y-1/2">
                          <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-md border-2 flex items-center justify-center transition-colors"
                            :class="form.order_bump_ids.includes(product.id) ? 'border-blue-500 bg-blue-500' : 'border-slate-300'">
<CheckIcon v-if="form.order_bump_ids.includes(product.id)" class="h-3 w-3 sm:h-4 sm:w-4 text-white" />
                          </div>
                        </div>

                        <div class="flex items-center gap-3 sm:gap-4 pl-8 sm:pl-10 min-w-0 w-full">
                          <img :src="`/${product.image}`" :alt="product.name"
                            class="w-12 h-12 sm:w-14 sm:h-14 rounded-lg object-cover border flex-shrink-0" />
                          
                          <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                              <h4 class="font-medium text-sm sm:text-base truncate">{{ product.name }}</h4>
                              <div class="text-sm sm:text-base font-semibold text-green-600 flex-shrink-0">
                                {{ formatPrice(product.price) }}
                              </div>
                            </div>
                            <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ product.description }}</p>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Lista de produtos selecionados -->
                    <div v-if="false" class="space-y-2">
                      <Label class="text-sm font-medium">Produtos selecionados ({{ form.order_bump_ids.length
                        }}):</Label>
                      <div class="space-y-2">
                        <div v-for="productId in form.order_bump_ids.filter(id => id != form.product_id)" :key="productId"
                          class="flex items-center justify-between p-2 sm:p-3 bg-green-50 border border-green-200 rounded-lg">
                          <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                            <img :src="`/${props.products.find(p => p.id === productId)?.image}`"
                              :alt="props.products.find(p => p.id === productId)?.name"
                              class="w-6 h-6 sm:w-8 sm:h-8 rounded-lg object-cover border flex-shrink-0" />
                            <div class="min-w-0 flex-1">
                              <div class="font-medium text-sm sm:text-base truncate">{{props.products.find(p => p.id ===
                                productId)?.name }}</div>
                              <div class="text-xs sm:text-sm text-muted-foreground">{{formatPrice(props.products.find(p =>
                                p.id === productId)?.price) }}</div>
                            </div>
                          </div>
                          <Button type="button" variant="outline" size="sm" @click="removeOrderBump(productId)"
                            class="text-red-600 hover:text-red-700 flex-shrink-0 ml-2">
                            <Trash2 class="h-3 w-3 sm:h-4 sm:w-4" />
                          </Button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Template Selection -->
                  <div v-if="false" class="space-y-3 sm:space-y-4">
                    <Label for="template" class="text-base font-semibold">Template do Checkout</Label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                      <div v-for="template in templates" :key="template.value"
                        @click="form.checkout_template = template.value"
                        class="relative p-3 sm:p-4 border-2 rounded-xl cursor-pointer transition-all hover:shadow-md touch-manipulation active:scale-95"
                        :class="form.checkout_template === template.value
                          ? 'border-blue-500 bg-blue-50 dark:bg-blue-950'
                          : 'border-slate-200 hover:border-slate-300'">
                        <div class="flex items-start gap-2 sm:gap-3">
                          <div :class="`p-2 ${template.color} rounded-lg text-white flex-shrink-0`">
                            <component :is="template.icon" class="h-4 w-4 sm:h-5 sm:w-5" />
                          </div>
                          <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-sm sm:text-base">{{ template.label }}</h4>
                            <p class="text-xs sm:text-sm text-muted-foreground mt-1 line-clamp-2">{{
                              template.description }}</p>
                          </div>
                        </div>
                        <CheckCircle v-if="form.checkout_template === template.value"
                          class="absolute top-2 right-2 sm:top-3 sm:right-3 h-4 w-4 sm:h-5 sm:w-5 text-blue-500" />
                      </div>
                    </div>
                  </div>

                  <!-- Layout Selection -->
                  <div class="space-y-2 sm:space-y-3">
                    <Label class="text-base font-medium">Layout da Página <Badge variant="outline" class="ml-1">
                        Obrigatório</Badge></Label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                      <div v-for="layout in layouts" :key="layout.value" @click="form.layout = layout.value"
                        class="relative p-2 sm:p-3 border rounded-lg cursor-pointer transition-all touch-manipulation active:scale-95"
                        :class="form.layout === layout.value
                          ? 'border-primary bg-primary/5'
                          : 'border-border hover:border-primary/50'">
                        <div class="flex items-center gap-2">
                          <div class="text-lg sm:text-xl font-mono bg-muted p-1 rounded flex-shrink-0">{{ layout.preview
                            }}</div>
                          <div class="min-w-0 flex-1">
                            <h4 class="font-medium text-xs sm:text-sm">{{ layout.label }}</h4>
                            <p class="text-xs text-muted-foreground line-clamp-1">{{ layout.description }}</p>
                          </div>
                        </div>
                        <CheckCircle v-if="form.layout === layout.value"
                          class="absolute top-1 right-1 sm:top-2 sm:right-2 h-3 w-3 sm:h-4 sm:w-4 text-primary" />
                      </div>
                    </div>
                  </div>
                </TabsContent>

                <!-- Design Tab -->
                <TabsContent value="design" class="space-y-6 sm:space-y-8">
                  <!-- Predefined Themes -->
                  <div class="space-y-3 sm:space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                      <div class="flex-1">
                        <Label class="text-base font-semibold">Temas Predefinidos</Label>
                        <p class="text-sm text-muted-foreground">
                          Escolha um tema pronto ou personalize as cores individualmente
                        </p>
                      </div>
                    </div>
                    
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                      <div v-for="theme in PREDEFINED_THEMES" :key="theme.id" 
                           class="relative p-4 border rounded-lg cursor-pointer transition-all hover:shadow-md"
                           :class="getCurrentTheme?.id === theme.id ? 'ring-2 ring-blue-500 bg-blue-50' : 'hover:bg-gray-50'"
                           @click="applyTheme(theme)">
                        <div class="flex items-center justify-between mb-3">
                          <h3 class="font-semibold text-gray-900">{{ theme.name }}</h3>
                          <Badge v-if="getCurrentTheme?.id === theme.id" variant="secondary">Ativo</Badge>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">{{ theme.description }}</p>
                        
                        <!-- Color Preview -->
                        <div class="grid grid-cols-4 gap-2">
                          <div class="w-6 h-6 rounded-full border" :style="`background-color: ${theme.colors.background_color}`" title="Fundo"></div>
                          <div class="w-6 h-6 rounded-full border" :style="`background-color: ${theme.colors.step_active_color}`" title="Step Ativo"></div>
                          <div class="w-6 h-6 rounded-full border" :style="`background-color: ${theme.colors.button_primary_color}`" title="Botão"></div>
                          <div class="w-6 h-6 rounded-full border" :style="`background-color: ${theme.colors.credit_card_icon_background_color}`" title="Cartão"></div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Banner Upload -->
                  <div class="space-y-3 sm:space-y-4">
                    <Label for="banner" class="text-base font-semibold">Banner</Label>
                    <p class="text-sm text-muted-foreground">
                      Adicione uma imagem de banner para o checkout (opcional)
                    </p>
                    <ImageUpload v-model="form.banner" label="Banner"
                      placeholder="Clique para fazer upload ou arraste uma imagem para o banner" />
                  </div>

                  <!-- Dark Mode -->
                  <div class="space-y-3 sm:space-y-4">
                    <div class="flex items-center justify-between p-3 sm:p-4 border rounded-lg">
                      <div class="flex-1">
                        <Label for="dark_mode" class="text-base font-semibold">Modo Escuro</Label>
                        <p class="text-sm text-muted-foreground">
                          Ative o tema escuro para o checkout
                        </p>
                      </div>
                      <Switch v-model="form.dark_mode" class="flex-shrink-0" />
                    </div>
                  </div>

                  <!-- Background Color -->
                  <div class="space-y-3 sm:space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                      <div class="flex-1">
                        <Label class="text-base font-semibold">Cor de Fundo do Checkout</Label>
                        <p class="text-sm text-muted-foreground">
                          Personalize a cor de fundo da página do checkout
                        </p>
                      </div>
                      <Button type="button" variant="outline" size="sm" @click="resetBackgroundColor"
                        :disabled="isBackgroundColorDefault" class="self-start sm:self-auto">
                        <RotateCcw class="h-3 w-3 mr-1" />
                        {{ isBackgroundColorDefault ? 'Cor Padrão' : 'Resetar Cor' }}
                      </Button>
                    </div>

                    <div class="grid gap-2">
                      <Label for="background_color">Cor de Fundo</Label>
                      <input v-model="form.background_color" type="color"
                        class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                    </div>

                    <!-- Preview da Cor de Fundo -->
                    <div class="space-y-2">
                      <Label class="text-sm font-medium">Preview da Cor de Fundo</Label>
                      <div class="flex justify-center p-3 sm:p-4 rounded-lg border-2 border-dashed border-gray-300"
                        :style="`background-color: ${form.background_color}`">
                        <div class="text-center">
                          <p class="text-sm font-medium" :class="getContrastColor(form.background_color)">
                            Esta será a cor de fundo do checkout
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Step Colors Customization -->
                  <div class="space-y-3 sm:space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                      <div class="flex-1">
                        <Label class="text-base font-semibold">Cores dos Steps</Label>
                        <p class="text-sm text-muted-foreground">
                          Personalize as cores dos steps do checkout
                        </p>
                      </div>
                      <Button type="button" variant="outline" size="sm" @click="resetStepColors"
                        :disabled="isStepColorsDefault" class="self-start sm:self-auto">
                        <RotateCcw class="h-3 w-3 mr-1" />
                        {{ isStepColorsDefault ? 'Cores Padrão' : 'Resetar Cores' }}
                      </Button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                      <div class="grid gap-2">
                        <Label for="step_active_color">Step Ativo</Label>
                        <input v-model="form.step_active_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                      </div>
                      <div class="grid gap-2">
                        <Label for="step_completed_color">Step Completado</Label>
                        <input v-model="form.step_completed_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                      </div>
                      <div class="grid gap-2">
                        <Label for="step_inactive_color">Step Inativo</Label>
                        <input v-model="form.step_inactive_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                      </div>
                      <div class="grid gap-2">
                        <Label for="step_text_color">Cor do Texto</Label>
                        <input v-model="form.step_text_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                      </div>
                    </div>

                    <!-- Preview dos Steps -->
                    <div class="space-y-2">
                      <Label class="text-sm font-medium">Preview dos Steps</Label>
                      <div class="flex justify-center p-3 sm:p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-2">
                          <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-medium"
                            :style="`background-color: ${form.step_completed_color}`">
                            ✓
                          </div>
                          <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-medium"
                            :style="`background-color: ${form.step_active_color}`">
                            2
                          </div>
                          <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-medium"
                            :style="`background-color: ${form.step_inactive_color}`">
                            3
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Payment Methods Configuration -->
                  <div class="space-y-3 sm:space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                      <div class="flex-1">
                        <Label class="text-base font-semibold">Métodos de Pagamento</Label>
                        <p class="text-sm text-muted-foreground">
                          Escolha quais métodos de pagamento estarão disponíveis no checkout
                        </p>
                      </div>
                    </div>
                    
                    <div class="space-y-3">
                      <div class="flex items-center justify-between p-3 border rounded-lg">
                        <div class="flex items-center space-x-3">
                          <Switch 
                            :model-value="form.enabled_payment_methods.includes('pix')"
                            @update:model-value="togglePaymentMethod('pix')"
                          />
                          <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 rounded-sm" :style="`background-color: ${form.pix_icon_background_color}`"></div>
                            <span class="text-sm font-medium">PIX</span>
                          </div>
                        </div>
                        <span class="text-xs text-gray-500">Pagamento instantâneo</span>
                      </div>
                      
                      <div class="flex items-center justify-between p-3 border rounded-lg">
                        <div class="flex items-center space-x-3">
                          <Switch 
                            :model-value="form.enabled_payment_methods.includes('credit_card')"
                            @update:model-value="togglePaymentMethod('credit_card')"
                          />
                          <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 rounded-sm" :style="`background-color: ${form.credit_card_icon_background_color}`"></div>
                            <span class="text-sm font-medium">Cartão de Crédito</span>
                          </div>
                        </div>
                        <span class="text-xs text-gray-500">Parcele em até 12x</span>
                      </div>
                      
                      <div class="flex items-center justify-between p-3 border rounded-lg">
                        <div class="flex items-center space-x-3">
                          <Switch 
                            :model-value="form.enabled_payment_methods.includes('boleto')"
                            @update:model-value="togglePaymentMethod('boleto')"
                          />
                          <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 rounded-sm" :style="`background-color: ${form.boleto_icon_background_color}`"></div>
                            <span class="text-sm font-medium">Boleto Bancário</span>
                          </div>
                        </div>
                        <span class="text-xs text-gray-500">Pague em até 3 dias</span>
                      </div>
                    </div>
                  </div>

                  <!-- Payment Method Colors Customization -->
                  <div class="space-y-3 sm:space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                      <div class="flex-1">
                        <Label class="text-base font-semibold">Cores dos Métodos de Pagamento</Label>
                        <p class="text-sm text-muted-foreground">
                          Personalize as cores de fundo dos ícones de cada método
                        </p>
                      </div>
                      <Button type="button" variant="outline" size="sm" @click="resetPaymentMethodColors"
                        :disabled="isPaymentMethodColorsDefault" class="self-start sm:self-auto">
                        <RotateCcw class="h-3 w-3 mr-1" />
                        {{ isPaymentMethodColorsDefault ? 'Cores Padrão' : 'Resetar Cores' }}
                      </Button>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                      <div class="grid gap-2">
                        <Label for="pix_icon_background_color">Cor do PIX</Label>
                        <input v-model="form.pix_icon_background_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                        <div class="flex items-center space-x-2">
                          <div class="w-6 h-6 rounded-sm" :style="`background-color: ${form.pix_icon_background_color}`"></div>
                          <span class="text-xs text-gray-500">PIX</span>
                        </div>
                      </div>
                      
                      <div class="grid gap-2">
                        <Label for="credit_card_icon_background_color">Cor do Cartão</Label>
                        <input v-model="form.credit_card_icon_background_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                        <div class="flex items-center space-x-2">
                          <div class="w-6 h-6 rounded-sm" :style="`background-color: ${form.credit_card_icon_background_color}`"></div>
                          <span class="text-xs text-gray-500">Cartão de Crédito</span>
                        </div>
                      </div>
                      
                      <div class="grid gap-2">
                        <Label for="boleto_icon_background_color">Cor do Boleto</Label>
                        <input v-model="form.boleto_icon_background_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                        <div class="flex items-center space-x-2">
                          <div class="w-6 h-6 rounded-sm" :style="`background-color: ${form.boleto_icon_background_color}`"></div>
                          <span class="text-xs text-gray-500">Boleto Bancário</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Payment Check Icon Colors Customization -->
                  <div class="space-y-3 sm:space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                      <div class="flex-1">
                        <Label class="text-base font-semibold">Cores dos Ícones de Check</Label>
                        <p class="text-sm text-muted-foreground">
                          Personalize as cores dos ícones de check (✓) que aparecem quando um método é selecionado
                        </p>
                      </div>
                      <Button type="button" variant="outline" size="sm" @click="resetPaymentMethodColors"
                        :disabled="isPaymentMethodColorsDefault" class="self-start sm:self-auto">
                        <RotateCcw class="h-3 w-3 mr-1" />
                        {{ isPaymentMethodColorsDefault ? 'Cores Padrão' : 'Resetar Cores' }}
                      </Button>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                      <div class="grid gap-2">
                        <Label for="pix_check_icon_background_color">Cor de Fundo do Check PIX</Label>
                        <input v-model="form.pix_check_icon_background_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                        <div class="flex items-center space-x-2">
                          <div class="w-6 h-6 rounded-sm flex items-center justify-center" :style="`background-color: ${form.pix_check_icon_background_color}`">
                            <Check class="w-3 h-3 text-white" />
                          </div>
                          <span class="text-xs text-gray-500">PIX</span>
                        </div>
                      </div>
                      
                      <div class="grid gap-2">
                        <Label for="credit_card_check_icon_background_color">Cor de Fundo do Check Cartão</Label>
                        <input v-model="form.credit_card_check_icon_background_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                        <div class="flex items-center space-x-2">
                          <div class="w-6 h-6 rounded-sm flex items-center justify-center" :style="`background-color: ${form.credit_card_check_icon_background_color}`">
                            <Check class="w-3 h-3 text-white" />
                          </div>
                          <span class="text-xs text-gray-500">Cartão de Crédito</span>
                        </div>
                      </div>
                      
                      <div class="grid gap-2">
                        <Label for="boleto_check_icon_background_color">Cor de Fundo do Check Boleto</Label>
                        <input v-model="form.boleto_check_icon_background_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                        <div class="flex items-center space-x-2">
                          <div class="w-6 h-6 rounded-sm flex items-center justify-center" :style="`background-color: ${form.boleto_check_icon_background_color}`">
                            <Check class="w-3 h-3 text-white" />
                          </div>
                          <span class="text-xs text-gray-500">Boleto Bancário</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Payment Icon Colors Customization -->
                  <div class="space-y-3 sm:space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                      <div class="flex-1">
                        <Label class="text-base font-semibold">Cores dos Ícones de Pagamento</Label>
                        <p class="text-sm text-muted-foreground">
                          Personalize as cores dos ícones de métodos de pagamento
                        </p>
                      </div>
                      <Button type="button" variant="outline" size="sm" @click="resetPaymentIconColors"
                        :disabled="isPaymentIconColorsDefault" class="self-start sm:self-auto">
                        <RotateCcw class="h-3 w-3 mr-1" />
                        {{ isPaymentIconColorsDefault ? 'Cores Padrão' : 'Resetar Cores' }}
                      </Button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                      <div class="grid gap-2">
                        <Label for="payment_icon_primary_color">Cor Primária</Label>
                        <input v-model="form.payment_icon_primary_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                      </div>
                      <div class="grid gap-2">
                        <Label for="payment_icon_secondary_color">Cor Secundária</Label>
                        <input v-model="form.payment_icon_secondary_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                      </div>
                      <div class="grid gap-2">
                        <Label for="payment_icon_background_color">Cor de Fundo</Label>
                        <input v-model="form.payment_icon_background_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                      </div>
                    </div>

                    <!-- Preview dos Ícones de Pagamento -->
                    <div class="space-y-2">
                      <Label class="text-sm font-medium">Preview dos Ícones</Label>
                      <div class="flex justify-center p-3 sm:p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                          <div class="w-12 h-12 rounded-lg flex items-center justify-center"
                            :style="`background-color: ${form.payment_icon_background_color}`">
                            <CreditCard class="w-6 h-6" :style="`color: ${form.payment_icon_primary_color}`" />
                          </div>
                          <div class="w-12 h-12 rounded-lg flex items-center justify-center"
                            :style="`background-color: ${form.payment_icon_background_color}`">
                            <div class="w-6 h-6 rounded-sm" :style="`background-color: ${form.payment_icon_secondary_color}`"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Button Color Customization -->
                  <div class="space-y-3 sm:space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                      <div class="flex-1">
                        <Label class="text-base font-semibold">Cor do Botão de Checkout</Label>
                        <p class="text-sm text-muted-foreground">
                          Personalize as cores do botão de finalizar compra
                        </p>
                      </div>
                      <Button type="button" variant="outline" size="sm" @click="resetButtonColors"
                        :disabled="isButtonColorsDefault" class="self-start sm:self-auto">
                        <RotateCcw class="h-3 w-3 mr-1" />
                        {{ isButtonColorsDefault ? 'Cores Padrão' : 'Resetar Cores' }}
                      </Button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                      <div class="grid gap-2">
                        <Label for="button_primary_color">Cor Primária</Label>
                        <input v-model="form.button_primary_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                      </div>
                      <div class="grid gap-2">
                        <Label for="button_secondary_color">Cor Secundária</Label>
                        <input v-model="form.button_secondary_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                      </div>
                    </div>

                    <!-- Preview do Botão -->
                    <div class="space-y-2">
                      <Label class="text-sm font-medium">Preview do Botão</Label>
                      <div class="flex justify-center p-3 sm:p-4 bg-gray-50 rounded-lg">
                        <button
                          class="px-4 sm:px-6 py-2 sm:py-3 text-white font-bold rounded-lg transition-all duration-200 shadow-lg text-sm sm:text-base"
                          :style="`background: linear-gradient(to right, ${form.button_primary_color}, ${form.button_secondary_color})`">
                          Finalizar Compra
                        </button>
                      </div>
                    </div>
                  </div>
                </TabsContent>

                <!-- Countdown Tab -->
                <TabsContent value="countdown" class="space-y-6 sm:space-y-8">
                  <!-- Countdown Toggle -->
                  <div class="space-y-3 sm:space-y-4">
                    <div class="flex items-center justify-between p-3 sm:p-4 border rounded-lg">
                      <div class="flex-1">
                        <Label for="countdown_enabled" class="text-base font-semibold">Contador Regressivo</Label>
                        <p class="text-sm text-muted-foreground">
                          Adicione urgência com um contador regressivo
                        </p>
                      </div>
                      <Switch v-model="form.countdown_enabled" class="flex-shrink-0" />
                    </div>
                  </div>

                  <div v-if="form.countdown_enabled"
                    class="space-y-4 sm:space-y-6 pl-4 sm:pl-6 border-l-2 border-gray-200">
                    <!-- Message -->
                    <div class="space-y-2">
                      <Label for="countdown_message">Mensagem</Label>
                      <Input v-model="form.countdown_message" placeholder="Ex: Oferta por tempo limitado!"
                        class="h-10 sm:h-12 input-mobile" />
                    </div>

                    <!-- Icon -->
                    <div class="space-y-2">
                      <Label for="countdown_icon">Ícone</Label>
                      <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                        <div v-for="icon in countdownIcons" :key="icon.value" @click="form.countdown_icon = icon.value"
                          class="relative p-2 sm:p-3 border-2 rounded-lg cursor-pointer transition-all hover:shadow-md touch-manipulation active:scale-95"
                          :class="form.countdown_icon === icon.value
                            ? 'border-blue-500 bg-blue-50'
                            : 'border-slate-200 hover:border-slate-300'">
                          <div class="flex items-center gap-2">
                            <component :is="icon.icon" class="h-3 w-3 sm:h-4 sm:w-4" />
                            <span class="text-xs sm:text-sm">{{ icon.label }}</span>
                          </div>
                          <CheckCircle v-if="form.countdown_icon === icon.value"
                            class="absolute top-1 right-1 sm:top-2 sm:right-2 h-3 w-3 sm:h-4 sm:w-4 text-blue-500" />
                        </div>
                      </div>
                    </div>

                    <!-- Icon Type -->
                    <div class="space-y-2">
                      <Label for="countdown_icon_type">Tipo de Ícone</Label>
                      <div class="flex gap-3 sm:gap-4">
                        <label class="flex items-center space-x-2 cursor-pointer">
                          <input type="radio" v-model="form.countdown_icon_type" value="icon"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4" />
                          <span class="text-sm">Ícone (Lucide)</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                          <input type="radio" v-model="form.countdown_icon_type" value="emoji"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4" />
                          <span class="text-sm">Emoji</span>
                        </label>
                      </div>
                    </div>

                    <!-- Duration -->
                    <div class="space-y-2">
                      <Label for="countdown_duration">Duração</Label>
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                        <div v-for="duration in countdownDurations" :key="duration.value"
                          @click="form.countdown_duration = duration.value"
                          class="relative p-2 sm:p-3 border-2 rounded-lg cursor-pointer transition-all hover:shadow-md touch-manipulation active:scale-95"
                          :class="form.countdown_duration === duration.value
                            ? 'border-blue-500 bg-blue-50'
                            : 'border-slate-200 hover:border-slate-300'">
                          <span class="text-xs sm:text-sm">{{ duration.label }}</span>
                          <CheckCircle v-if="form.countdown_duration === duration.value"
                            class="absolute top-1 right-1 sm:top-2 sm:right-2 h-3 w-3 sm:h-4 sm:w-4 text-blue-500" />
                        </div>
                      </div>
                    </div>

                    <!-- Colors -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                      <div class="space-y-2">
                        <Label for="countdown_bg_color">Cor de Fundo</Label>
                        <input v-model="form.countdown_bg_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                      </div>
                      <div class="space-y-2">
                        <Label for="countdown_text_color">Cor do Texto</Label>
                        <input v-model="form.countdown_text_color" type="color"
                          class="w-full h-10 sm:h-12 border border-gray-300 rounded-md cursor-pointer input-mobile" />
                      </div>
                    </div>
                  </div>
                </TabsContent>


              </Tabs>
            </CardContent>
          </Card>
        </div>


      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Melhorias para touch em dispositivos móveis */
.touch-manipulation {
  touch-action: manipulation;
}

/* Melhor scroll em mobile */
::-webkit-scrollbar {
  width: 4px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 2px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.3);
}

/* Line clamp para textos longos */
.line-clamp-1 {
  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 1;
}

.line-clamp-2 {
  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

/* Melhor espaçamento para inputs de cor em mobile */
input[type="color"] {
  -webkit-appearance: none;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
}

input[type="color"]::-webkit-color-swatch-wrapper {
  padding: 0;
  border-radius: 0.375rem;
}

input[type="color"]::-webkit-color-swatch {
  border: none;
  border-radius: 0.375rem;
}

/* Melhor foco em mobile */
/* *:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
} */

/* Melhor touch target size */
button,
input,
select,
textarea {
  min-height: 44px;
}

/* Melhor espaçamento para checkboxes e radios */
input[type="checkbox"],
input[type="radio"] {
  min-width: 20px;
  min-height: 20px;
}

/* Melhorias para mobile */
@media (max-width: 640px) {
  .grid {
    grid-template-columns: 1fr;
  }
  
  .space-y-6 > * + * {
    margin-top: 1.5rem;
  }
  
  .space-y-8 > * + * {
    margin-top: 2rem;
  }
  
  /* Melhor espaçamento em mobile */
  .p-3 {
    padding: 0.75rem;
  }
  
  .p-4 {
    padding: 1rem;
  }
  
  .p-6 {
    padding: 1.5rem;
  }
  
  .gap-3 {
    gap: 0.75rem;
  }
  
  .gap-4 {
    gap: 1rem;
  }
  
  .gap-6 {
    gap: 1.5rem;
  }
  
  /* Melhor responsividade para tabs */
  .grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  
  .grid-cols-4 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  
  /* Melhor visualização em telas pequenas */
  .text-2xl {
    font-size: 1.5rem;
  }
  
  .text-3xl {
    font-size: 1.875rem;
  }
  
  .text-base {
    font-size: 0.875rem;
  }
  
  .text-lg {
    font-size: 1rem;
  }
  
  /* Melhor touch feedback */
  .active\:scale-95:active {
    transform: scale(0.95);
  }
  
  /* Melhor contraste para acessibilidade */
  .text-muted-foreground {
    color: #6b7280;
  }
  
  /* Melhor navegação mobile */
  .mobile-menu {
    animation: slideDown 0.2s ease-out;
  }
  
  /* Melhor scroll em containers */
  .overflow-y-auto {
    -webkit-overflow-scrolling: touch;
  }
  
  /* Melhor foco em elementos interativos */
  /* .cursor-pointer:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
  } */
  
  /* Melhor feedback visual para seleções */
  /* .border-blue-500 {
    box-shadow: 0 0 0 1px #3b82f6;
  } */
  
  /* Melhor espaçamento para cards */
  .card-mobile {
    margin: 0.5rem;
    border-radius: 0.75rem;
  }
  
  /* Melhor tamanho de fonte em mobile */
  .text-xs {
    font-size: 0.75rem;
  }
  
  .text-sm {
    font-size: 0.875rem;
  }
  
  /* Melhor padding para botões em mobile */
  .btn-mobile {
    padding: 0.75rem 1rem;
    min-height: 44px;
  }
  
  /* Melhor espaçamento para inputs */
  .input-mobile {
    padding: 0.75rem;
    min-height: 44px;
  }
}

/* Animações suaves */
.transition-all {
  transition: all 0.2s ease-in-out;
}

/* Animação para menu mobile */
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Melhor hover em desktop */
@media (min-width: 641px) {
  .hover\:shadow-md:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  }
  
  .hover\:border-slate-300:hover {
    border-color: #cbd5e1;
  }
  

}

/* Melhor acessibilidade */
@media (prefers-reduced-motion: reduce) {
  .transition-all {
    transition: none;
  }
  
  .active\:scale-95:active {
    transform: none;
  }
}

/* Melhor contraste para modo escuro */
@media (prefers-color-scheme: dark) {
  .text-muted-foreground {
    color: #9ca3af;
  }
  
  .bg-gray-50 {
    background-color: #1f2937;
  }
  
  .border-slate-200 {
    border-color: #374151;
  }
  
  .hover\:bg-gray-50:hover {
    background-color: #374151;
  }
}

/* Melhor performance em dispositivos com tela de baixa resolução */
@media (max-resolution: 1dppx) {
  .text-xs {
    font-size: 0.8125rem;
  }
  
  .text-sm {
    font-size: 0.9375rem;
  }
}

/* Melhor suporte para dispositivos com tela de alta densidade */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .border {
    border-width: 0.5px;
  }
}

/* Melhor suporte para orientação landscape em mobile */
@media (max-width: 640px) and (orientation: landscape) {
  .max-h-48 {
    max-height: 12rem;
  }
  
  .max-h-60 {
    max-height: 15rem;
  }
}

/* Melhor suporte para dispositivos com tela muito pequena */
@media (max-width: 360px) {
  .text-2xl {
    font-size: 1.25rem;
  }
  
  .text-3xl {
    font-size: 1.5rem;
  }
  
  .p-3 {
    padding: 0.5rem;
  }
  
  .p-4 {
    padding: 0.75rem;
  }
  
  .gap-3 {
    gap: 0.5rem;
  }
  
  .gap-4 {
    gap: 0.75rem;
  }
}
</style>