<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import 'vue3-toastify/dist/index.css'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Switch } from '@/components/ui/switch'
import {
  Trash2,
  Loader2,
  Copy,
  ExternalLink,
  Save,
  LayoutGrid,
  Box,
} from 'lucide-vue-next'

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

const props = defineProps({
  checkout: Object,
  products: Array,
})

const breadcrumbs = computed(() => [])

// Usar composables
const {
  form,
  countdownDurations,
  isSubmitting,
  showDeleteDialog,
  hoveredPrimary,
  hoveredSecondary,
  newFieldKey,
  newFieldLabel,
  formatPrice,
  addCustomField,
  removeField,
  toggleAllFields,
  resetAllOrderBumpColors,
  resetBackgroundColors,
  resetButtonColors,
  resetCountdownColors,
  resetStepsColors,
  availableProducts,
  selectedProduct
} = useCheckoutForm(props.checkout)

const {
  submitForm,
  deleteCheckout,
  copyCheckoutUrl,
  openCheckout
} = useCheckoutActions(props.checkout)

// Função de submit
const submitFormHandler = () => {
  submitForm(form, isSubmitting)
}

// Função de delete
const deleteCheckoutHandler = () => {
  deleteCheckout(props.checkout.id)
}

// Função de copy URL
const copyCheckoutUrlHandler = () => {
  copyCheckoutUrl(props.checkout.id)
}

// Função de open checkout
const openCheckoutHandler = () => {
  openCheckout(props.checkout.id)
}

// Expor produtos para o composable
window.products = props.products
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Editar Checkout" />
    
    <div class="flex h-full flex-1 flex-col w-full">
      <!-- Header fixo otimizado para mobile -->
      <div class="sticky top-0 z-20 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b">
        <div class="px-3 py-3 md:px-6 md:py-4">
          <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
              <h1 class="text-lg font-bold text-foreground md:text-2xl lg:text-3xl truncate">
                Editar Checkout
              </h1>
              <p class="text-xs text-muted-foreground mt-1 md:text-sm">
                Personalize a aparência e configurações
              </p>
            </div>
            
            <!-- Botões de ação do header -->
            <div class="flex items-center gap-2 flex-shrink-0">
              <Button 
                variant="outline" 
                @click="copyCheckoutUrlHandler" 
                class="flex items-center gap-1.5 text-xs h-8 px-3 md:h-10 md:px-4 md:text-sm"
              >
                <Copy class="w-3 h-3 md:w-4 md:h-4" />
                <span class="hidden sm:inline">Copiar URL</span>
                <span class="sm:hidden">Copiar</span>
              </Button>
              
              <Button 
                @click="openCheckoutHandler" 
                class="flex items-center gap-1.5 text-xs h-8 px-3 md:h-10 md:px-4 md:text-sm"
              >
                <ExternalLink class="w-3 h-3 md:w-4 md:h-4" />
                <span class="hidden sm:inline">Visualizar</span>
                <span class="sm:hidden">Ver</span>
              </Button>
            </div>
          </div>
        </div>
      </div>

      <!-- Conteúdo principal -->
      <div class="flex-1 flex flex-col min-h-0">
        <form @submit.prevent="submitFormHandler" class="flex-1 flex flex-col min-h-0">
          <Tabs default-value="produto" class="flex-1 flex flex-col min-h-0">
            <!-- TabsList otimizada para mobile -->
            <div class="sticky top-[73px] md:top-[89px] z-10 bg-background border-b shadow-sm flex-shrink-0">
              <div class="px-3 py-2 md:px-6">
                <TabsList class="w-full h-auto p-1 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-1 bg-muted/50">
                  <TabsTrigger 
                    value="produto" 
                    class="text-[10px] md:text-sm px-1.5 py-2 md:px-3 data-[state=active]:bg-background data-[state=active]:text-foreground"
                  >
                    Produto
                  </TabsTrigger>
                  <TabsTrigger 
                    value="aparencia" 
                    class="text-[10px] md:text-sm px-1.5 py-2 md:px-3 data-[state=active]:bg-background data-[state=active]:text-foreground"
                  >
                    Aparência
                  </TabsTrigger>
                  <TabsTrigger 
                    value="formulario" 
                    class="text-[10px] md:text-sm px-1.5 py-2 md:px-3 data-[state=active]:bg-background data-[state=active]:text-foreground"
                  >
                    Formulário
                  </TabsTrigger>
                  <TabsTrigger 
                    value="pagamento" 
                    class="text-[10px] md:text-sm px-1.5 py-2 md:px-3 data-[state=active]:bg-background data-[state=active]:text-foreground"
                  >
                    Pagamento
                  </TabsTrigger>
                  <TabsTrigger 
                    value="cores" 
                    class="text-[10px] md:text-sm px-1.5 py-2 md:px-3 data-[state=active]:bg-background data-[state=active]:text-foreground"
                  >
                    Cores
                  </TabsTrigger>
                  <TabsTrigger 
                    value="contagem" 
                    class="text-[10px] md:text-sm px-1.5 py-2 md:px-3 data-[state=active]:bg-background data-[state=active]:text-foreground"
                  >
                    Contagem
                  </TabsTrigger>
                  <TabsTrigger 
                    value="order-bump" 
                    class="text-[10px] md:text-sm px-1.5 py-2 md:px-3 data-[state=active]:bg-background data-[state=active]:text-foreground col-span-2 md:col-span-1"
                  >
                    Order Bump
                  </TabsTrigger>
                </TabsList>
              </div>
            </div>

            <!-- Conteúdo das tabs com scroll -->
            <div class="flex-1 overflow-y-auto min-h-0">
              <div class="px-3 py-4 md:px-6 md:py-6">
                <!-- Tab: Produto -->
                <TabsContent value="produto" class="space-y-4 md:space-y-6">
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
                <TabsContent value="aparencia" class="space-y-4 md:space-y-6">
                  <AppearanceSettings 
                    :form="form"
                    :banner-preview="props.checkout.banner" 
                  />
                </TabsContent>

                <!-- Tab: Formulário -->
                <TabsContent value="formulario" class="space-y-4 md:space-y-6">
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
                <TabsContent value="pagamento" class="space-y-4 md:space-y-6">
                  <PaymentMethodsSettings :form="form" />
                </TabsContent>

                <!-- Tab: Cores -->
                <TabsContent value="cores" class="space-y-4 md:space-y-6">
                  <ColorSettings 
                    :form="form"
                    :hovered-primary="hoveredPrimary"
                    :hovered-secondary="hoveredSecondary"
                    :show-reset-buttons="true"
                    :reset-background-colors="resetBackgroundColors"
                    :reset-button-colors="resetButtonColors"
                    :reset-steps-colors="resetStepsColors" 
                  />
                </TabsContent>

                <!-- Tab: Contagem Regressiva -->
                <TabsContent value="contagem" class="space-y-4 md:space-y-6">
                  <CountdownSettings 
                    :form="form"
                    :countdown-durations="countdownDurations"
                    :show-reset-buttons="true"
                    :reset-countdown-colors="resetCountdownColors" 
                  />
                </TabsContent>

                <!-- Tab: Order Bump Customization -->
                <TabsContent value="order-bump" class="space-y-4 md:space-y-6">
                  <Card>
                    <CardHeader class="px-4 py-3 md:px-6 md:py-4">
                      <CardTitle class="flex items-center gap-2 text-sm md:text-base">
                        <LayoutGrid class="h-4 w-4 md:h-5 md:w-5" />
                        Personalização do Order Bump
                      </CardTitle>
                      <CardDescription class="text-xs md:text-sm">
                        Configure a aparência e o comportamento do Order Bump
                      </CardDescription>
                    </CardHeader>
                    
                    <CardContent class="px-4 py-3 md:px-6 md:py-4">
                      <div class="space-y-4 md:space-y-6">
                        <!-- Switch Order Bump -->
                        <div class="flex items-center space-x-3">
                          <Switch v-model="form.order_bump_enabled" id="order_bump_enabled" />
                          <Label for="order_bump_enabled" class="font-medium flex items-center gap-2 text-sm md:text-base">
                            <Box class="h-3 w-3 md:h-4 md:w-4" />
                            Order Bump Ativo
                          </Label>
                        </div>

                        <!-- Configurações do Order Bump -->
                        <div v-show="form.order_bump_enabled" class="space-y-4 md:space-y-6">
                          <!-- Header com reset -->
                          <div class="flex items-center justify-between">
                            <h4 class="font-medium text-sm md:text-base">Cores do Order Bump</h4>
                            <Button 
                              @click="resetAllOrderBumpColors()" 
                              variant="outline" 
                              size="sm" 
                              class="text-xs h-8 px-3"
                            >
                              Reset Cores
                            </Button>
                          </div>

                          <!-- Grid responsivo para campos de cor -->
                          <div class="grid gap-4 md:gap-6">
                            <!-- Cor de fundo -->
                            <div class="space-y-2">
                              <Label for="order_bump_bg_color" class="text-xs md:text-sm font-medium">
                                Cor de fundo do Order Bump
                              </Label>
                              <div class="flex items-center gap-2">
                                <input 
                                  type="color" 
                                  v-model="form.order_bump_bg_color" 
                                  id="order_bump_bg_color"
                                  class="w-10 h-10 rounded-md border border-input cursor-pointer flex-shrink-0" 
                                />
                                <Input 
                                  v-model="form.order_bump_bg_color" 
                                  class="flex-1 text-xs md:text-sm h-10" 
                                />
                                <Button 
                                  @click="form.order_bump_bg_color = '#ffffff'" 
                                  variant="outline" 
                                  size="sm"
                                  class="text-xs h-10 px-3 flex-shrink-0"
                                >
                                  Reset
                                </Button>
                              </div>
                            </div>

                            <!-- Cor do texto -->
                            <div class="space-y-2">
                              <Label for="order_bump_text_color" class="text-xs md:text-sm font-medium">
                                Cor do texto do Order Bump
                              </Label>
                              <div class="flex items-center gap-2">
                                <input 
                                  type="color" 
                                  v-model="form.order_bump_text_color" 
                                  id="order_bump_text_color"
                                  class="w-10 h-10 rounded-md border border-input cursor-pointer flex-shrink-0" 
                                />
                                <Input 
                                  v-model="form.order_bump_text_color" 
                                  class="flex-1 text-xs md:text-sm h-10" 
                                />
                                <Button 
                                  @click="form.order_bump_text_color = '#0f172a'" 
                                  variant="outline" 
                                  size="sm"
                                  class="text-xs h-10 px-3 flex-shrink-0"
                                >
                                  Reset
                                </Button>
                              </div>
                            </div>

                            <!-- Cor da borda -->
                            <div class="space-y-2">
                              <Label for="order_bump_border_color" class="text-xs md:text-sm font-medium">
                                Cor da borda do Order Bump
                              </Label>
                              <div class="flex items-center gap-2">
                                <input 
                                  type="color" 
                                  v-model="form.order_bump_border_color" 
                                  id="order_bump_border_color"
                                  class="w-10 h-10 rounded-md border border-input cursor-pointer flex-shrink-0" 
                                />
                                <Input 
                                  v-model="form.order_bump_border_color" 
                                  class="flex-1 text-xs md:text-sm h-10" 
                                />
                                <Button 
                                  @click="form.order_bump_border_color = '#fbbf24'" 
                                  variant="outline" 
                                  size="sm"
                                  class="text-xs h-10 px-3 flex-shrink-0"
                                >
                                  Reset
                                </Button>
                              </div>
                            </div>

                            <!-- Campos de texto -->
                            <div class="grid gap-4 md:grid-cols-2">
                              <div class="space-y-2">
                                <Label for="order_bump_description" class="text-xs md:text-sm font-medium">
                                  Descrição do Order Bump
                                </Label>
                                <Input 
                                  v-model="form.order_bump_description" 
                                  id="order_bump_description"
                                  placeholder="Ex: Este produto é uma ótima opção..." 
                                  class="text-xs md:text-sm h-10" 
                                />
                              </div>

                              <div class="space-y-2">
                                <Label for="order_bump_cta_text" class="text-xs md:text-sm font-medium">
                                  Texto do CTA
                                </Label>
                                <Input 
                                  v-model="form.order_bump_cta_text" 
                                  id="order_bump_cta_text"
                                  placeholder="Ex: Quero comprar também!" 
                                  class="text-xs md:text-sm h-10" 
                                />
                              </div>
                            </div>

                            <!-- Cores do CTA -->
                            <div class="grid gap-4 md:grid-cols-2">
                              <div class="space-y-2">
                                <Label for="order_bump_cta_bg_color" class="text-xs md:text-sm font-medium">
                                  Cor de fundo do CTA
                                </Label>
                                <div class="flex items-center gap-2">
                                  <input 
                                    type="color" 
                                    v-model="form.order_bump_cta_bg_color" 
                                    id="order_bump_cta_bg_color"
                                    class="w-10 h-10 rounded-md border border-input cursor-pointer flex-shrink-0" 
                                  />
                                  <Input 
                                    v-model="form.order_bump_cta_bg_color" 
                                    class="flex-1 text-xs md:text-sm h-10" 
                                  />
                                  <Button 
                                    @click="form.order_bump_cta_bg_color = '#10b981'" 
                                    variant="outline" 
                                    size="sm"
                                    class="text-xs h-10 px-3 flex-shrink-0"
                                  >
                                    Reset
                                  </Button>
                                </div>
                              </div>

                              <div class="space-y-2">
                                <Label for="order_bump_cta_text_color" class="text-xs md:text-sm font-medium">
                                  Cor do texto do CTA
                                </Label>
                                <div class="flex items-center gap-2">
                                  <input 
                                    type="color" 
                                    v-model="form.order_bump_cta_text_color" 
                                    id="order_bump_cta_text_color"
                                    class="w-10 h-10 rounded-md border border-input cursor-pointer flex-shrink-0" 
                                  />
                                  <Input 
                                    v-model="form.order_bump_cta_text_color" 
                                    class="flex-1 text-xs md:text-sm h-10" 
                                  />
                                  <Button 
                                    @click="form.order_bump_cta_text_color = '#ffffff'" 
                                    variant="outline" 
                                    size="sm"
                                    class="text-xs h-10 px-3 flex-shrink-0"
                                  >
                                    Reset
                                  </Button>
                                </div>
                              </div>
                            </div>

                            <!-- Texto de recomendação -->
                            <div class="grid gap-4 md:grid-cols-2">
                              <div class="space-y-2">
                                <Label for="order_bump_recommended_text" class="text-xs md:text-sm font-medium">
                                  Texto de recomendação
                                </Label>
                                <Input 
                                  v-model="form.order_bump_recommended_text" 
                                  id="order_bump_recommended_text"
                                  placeholder="Ex: (Recomendado)" 
                                  class="text-xs md:text-sm h-10" 
                                />
                              </div>

                              <div class="space-y-2">
                                <Label for="order_bump_recommended_color" class="text-xs md:text-sm font-medium">
                                  Cor do texto de recomendação
                                </Label>
                                <div class="flex items-center gap-2">
                                  <input 
                                    type="color" 
                                    v-model="form.order_bump_recommended_color"
                                    id="order_bump_recommended_color"
                                    class="w-10 h-10 rounded-md border border-input cursor-pointer flex-shrink-0" 
                                  />
                                  <Input 
                                    v-model="form.order_bump_recommended_color" 
                                    class="flex-1 text-xs md:text-sm h-10" 
                                  />
                                  <Button 
                                    @click="form.order_bump_recommended_color = '#fbbf24'" 
                                    variant="outline" 
                                    size="sm"
                                    class="text-xs h-10 px-3 flex-shrink-0"
                                  >
                                    Reset
                                  </Button>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                </TabsContent>
              </div>
            </div>
          </Tabs>
        </form>
      </div>

      <!-- Footer fixo com botões de ação -->
      <div class="sticky bottom-0 z-10 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-t">
        <div class="px-3 py-3 md:px-6 md:py-4">
          <div class="flex flex-col sm:flex-row gap-3 justify-end">
            <Button 
              type="button" 
              variant="outline" 
              @click="showDeleteDialog = true" 
              class="flex items-center justify-center gap-2 w-full sm:w-auto text-xs h-10 md:text-sm md:h-11"
            >
              <Trash2 class="w-3 h-3 md:w-4 md:h-4" />
              <span class="hidden sm:inline">Excluir Checkout</span>
              <span class="sm:hidden">Excluir</span>
            </Button>
            
            <Button 
              type="submit" 
              :disabled="isSubmitting" 
              @click="submitFormHandler"
              class="flex items-center justify-center gap-2 w-full sm:w-auto text-xs h-10 md:text-sm md:h-11 min-w-[120px]"
            >
              <Loader2 v-if="isSubmitting" class="w-3 h-3 md:w-4 md:h-4 animate-spin" />
              <Save v-else class="w-3 h-3 md:w-4 md:h-4" />
              <span v-if="isSubmitting" class="hidden sm:inline">Salvando...</span>
              <span v-else class="hidden sm:inline">Salvar Alterações</span>
              <span v-if="isSubmitting" class="sm:hidden">Salvando...</span>
              <span v-else class="sm:hidden">Salvar</span>
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="showDeleteDialog">
      <DialogContent class="sm:max-w-md mx-3 sm:mx-0">
        <DialogHeader>
          <DialogTitle class="text-base md:text-lg">Confirmar Exclusão</DialogTitle>
          <DialogDescription class="text-sm md:text-base">
            Tem certeza que deseja excluir este checkout? Esta ação não pode ser desfeita.
          </DialogDescription>
        </DialogHeader>
        <div class="flex flex-col sm:flex-row justify-end gap-3 mt-4">
          <Button 
            variant="outline" 
            @click="showDeleteDialog = false" 
            class="w-full sm:w-auto h-10 text-sm"
          >
            Cancelar
          </Button>
          <Button 
            variant="destructive" 
            @click="deleteCheckoutHandler" 
            class="w-full sm:w-auto h-10 text-sm"
          >
            Excluir
          </Button>
        </div>
      </DialogContent>
    </Dialog>
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
  .overflow-y-auto {
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
  }
  
  .overflow-y-auto::-webkit-scrollbar {
    width: 2px;
  }
  
  .overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
  }
  
  .overflow-y-auto::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 1px;
  }
}

/* Ajustar altura mínima para evitar problemas de viewport em mobile */
@media (max-width: 768px) {
  .min-h-screen {
    min-height: 100vh;
    min-height: 100dvh; /* Dynamic viewport height para navegadores modernos */
  }
}

/* Melhorar a aparência dos inputs de cor em mobile */
input[type="color"] {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background-color: transparent;
  border: none;
  cursor: pointer;
}

input[type="color"]::-webkit-color-swatch-wrapper {
  padding: 0;
  border: none;
  border-radius: 6px;
}

input[type="color"]::-webkit-color-swatch {
  border: none;
  border-radius: 6px;
}

input[type="color"]::-moz-color-swatch {
  border: none;
  border-radius: 6px;
}
</style>