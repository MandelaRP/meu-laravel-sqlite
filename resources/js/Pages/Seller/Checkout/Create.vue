<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import 'vue3-toastify/dist/index.css';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

// Componentes reutilizáveis
import ProductSelection from '@/components/checkout/ProductSelection.vue'
import OrderBumpsSelection from '@/components/checkout/OrderBumpsSelection.vue'
import AppearanceSettings from '@/components/checkout/AppearanceSettings.vue'
import FormFieldsSettings from '@/components/checkout/FormFieldsSettings.vue'
import PaymentMethodsSettings from '@/components/checkout/PaymentMethodsSettings.vue'
import ColorSettings from '@/components/checkout/ColorSettings.vue'
import CountdownSettings from '@/components/checkout/CountdownSettings.vue'

// Composables
import { useCheckoutForm } from '@/composables/useCheckoutForm'
import { useCheckoutActions } from '@/composables/useCheckoutActions'

const breadcrumbs = [];

const props = defineProps({
  products: Array,
});

// Usar composables
const {
  form,
  countdownDurations,
  isSubmitting,
  hoveredPrimary,
  hoveredSecondary,
  newFieldKey,
  newFieldLabel,
  formatPrice,
  addCustomField,
  removeField,
  toggleAllFields,
  availableProducts,
  selectedProduct
} = useCheckoutForm()

const { submitForm } = useCheckoutActions()

// Função de submit
const submit = () => {
  submitForm(form, isSubmitting)
}

// Expor produtos para o composable
window.products = props.products
</script>

<template>
  <Head title="Criar Checkout" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col w-full">
      <!-- Header - Otimizado para mobile -->
      <div class="sticky top-0 z-10 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b px-3 py-4 md:px-6">
        <div class="max-w-5xl mx-auto">
          <h1 class="text-lg md:text-2xl font-semibold tracking-tight">
            Criar Checkout
          </h1>
          <p class="text-xs md:text-sm text-muted-foreground mt-1">
            Configure as opções do seu checkout
          </p>
        </div>
      </div>

      <!-- Conteúdo principal -->
      <div class="flex-1 px-3 py-4 md:px-6 md:py-6 max-w-5xl mx-auto w-full">
        <form @submit.prevent="submit" class="space-y-4 md:space-y-8">
          <Tabs default-value="produto" class="w-full">
            <!-- TabsList otimizada para mobile -->
            <div class="sticky top-[73px] md:top-[89px] z-10 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 pb-2 -mx-3 px-3 md:mx-0 md:px-0">
              <TabsList class="w-full h-auto p-1 grid grid-cols-3 md:grid-cols-6 gap-1 bg-muted/50">
                <TabsTrigger 
                  value="produto" 
                  class="text-[10px] md:text-sm px-2 py-2 md:px-3 md:py-2 data-[state=active]:bg-background data-[state=active]:text-foreground"
                >
                  Produto
                </TabsTrigger>
                <TabsTrigger 
                  value="aparencia" 
                  class="text-[10px] md:text-sm px-2 py-2 md:px-3 md:py-2 data-[state=active]:bg-background data-[state=active]:text-foreground"
                >
                  Aparência
                </TabsTrigger>
                <TabsTrigger 
                  value="formulario" 
                  class="text-[10px] md:text-sm px-2 py-2 md:px-3 md:py-2 data-[state=active]:bg-background data-[state=active]:text-foreground"
                >
                  Formulário
                </TabsTrigger>
                <TabsTrigger 
                  value="pagamento" 
                  class="text-[10px] md:text-sm px-2 py-2 md:px-3 md:py-2 data-[state=active]:bg-background data-[state=active]:text-foreground"
                >
                  Pagamento
                </TabsTrigger>
                <TabsTrigger 
                  value="cores" 
                  class="text-[10px] md:text-sm px-2 py-2 md:px-3 md:py-2 data-[state=active]:bg-background data-[state=active]:text-foreground"
                >
                  Cores
                </TabsTrigger>
                <TabsTrigger 
                  value="contagem" 
                  class="text-[10px] md:text-sm px-2 py-2 md:px-3 md:py-2 data-[state=active]:bg-background data-[state=active]:text-foreground"
                >
                  Contagem
                </TabsTrigger>
              </TabsList>
            </div>

            <!-- Conteúdo das tabs com padding otimizado -->
            <div class="mt-4 md:mt-6">
              <!-- Tab: Produto -->
              <TabsContent value="produto" class="space-y-4 md:space-y-6 mt-0">
                <ProductSelection 
                  :form="form"
                  :products="props.products"
                  :selected-product="selectedProduct"
                  :format-price="formatPrice" 
                />
                <OrderBumpsSelection 
                  :form="form"
                  :available-products="availableProducts"
                  :format-price="formatPrice" 
                />
              </TabsContent>

              <!-- Tab: Aparência -->
              <TabsContent value="aparencia" class="space-y-4 md:space-y-6 mt-0">
                <AppearanceSettings 
                  :form="form"
                  :banner-preview="null" 
                />
              </TabsContent>

              <!-- Tab: Formulário -->
              <TabsContent value="formulario" class="space-y-4 md:space-y-6 mt-0">
                <FormFieldsSettings 
                  :form="form"
                  v-model:new-field-key="newFieldKey"
                  v-model:new-field-label="newFieldLabel"
                  :toggle-all-fields="toggleAllFields"
                  :add-custom-field="addCustomField"
                  :remove-field="removeField" 
                />
              </TabsContent>

              <!-- Tab: Pagamento -->
              <TabsContent value="pagamento" class="space-y-4 md:space-y-6 mt-0">
                <PaymentMethodsSettings :form="form" />
              </TabsContent>

              <!-- Tab: Cores -->
              <TabsContent value="cores" class="space-y-4 md:space-y-6 mt-0">
                <ColorSettings 
                  :form="form"
                  :hovered-primary="hoveredPrimary"
                  :hovered-secondary="hoveredSecondary"
                  :show-reset-buttons="false" 
                />
              </TabsContent>

              <!-- Tab: Contagem Regressiva -->
              <TabsContent value="contagem" class="space-y-4 md:space-y-6 mt-0">
                <CountdownSettings 
                  :form="form"
                  :countdown-durations="countdownDurations"
                  :show-reset-buttons="false" 
                />
              </TabsContent>
            </div>
          </Tabs>
        </form>
      </div>

      <!-- Footer com botões - Fixo no mobile -->
      <div class="sticky bottom-0 z-10 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-t px-3 py-4 md:px-6">
        <div class="max-w-5xl mx-auto">
          <div class="flex flex-col sm:flex-row justify-end gap-3 md:gap-4">
            <Button 
              type="button" 
              variant="outline" 
              class="w-full sm:w-auto text-sm md:text-base h-10 md:h-11"
            >
              Cancelar
            </Button>
            <Button 
              type="submit" 
              :disabled="isSubmitting" 
              class="w-full sm:w-auto text-sm md:text-base h-10 md:h-11 min-w-[120px]"
              @click="submit"
            >
              <span v-if="isSubmitting">Criando...</span>
              <template v-else>
                <span class="hidden sm:inline">Criar Checkout</span>
                <span class="sm:hidden">Criar</span>
              </template>
            </Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Garantir que o backdrop blur funcione em todos os navegadores */
@supports (backdrop-filter: blur(8px)) {
  .backdrop-blur {
    backdrop-filter: blur(8px);
  }
}

/* Melhorar a rolagem em dispositivos touch */
@media (max-width: 768px) {
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
  }
  
  .overflow-x-auto::-webkit-scrollbar {
    display: none;
  }
}

/* Ajustar altura mínima para evitar problemas de viewport em mobile */
@media (max-width: 768px) {
  .min-h-screen {
    min-height: 100vh;
    min-height: 100dvh; /* Dynamic viewport height para navegadores modernos */
  }
}
</style>