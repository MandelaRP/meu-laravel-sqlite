import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'

export function useCheckoutForm(checkout = null) {
  // Constantes comuns
  const countdownDurations = [
    { value: 900, label: '15 minutos' },
    { value: 1800, label: '30 minutos' },
    { value: 3600, label: '1 hora' },
    { value: 7200, label: '2 horas' },
    { value: 14400, label: '4 horas' },
    { value: 28800, label: '8 horas' },
    { value: 86400, label: '24 horas' },
  ]

  // Configuração padrão dos métodos de pagamento
  const defaultPaymentMethods = [
    {
      name: 'pix',
      label: 'PIX',
      icon: 'pix',
      image: '/images/icons/icon-pix.png',
      show_image: true,
      icon_color: '#ffffff',
      icon_bg_color: '#dbdbdb',
      enabled: true,
    },
    {
      name: 'credit_card',
      label: 'Cartão de Crédito',
      icon: 'credit_card',
      image: '/images/icons/icon-credit-card.png',
      show_image: false,
      icon_color: '#ffffff',
      icon_bg_color: '#2980b9',
      enabled: true,
    },
    {
      name: 'boleto',
      label: 'Boleto',
      icon: 'boleto',
      image: '/images/icons/icon-boleto.png',
      show_image: false,
      icon_color: '#ffffff',
      icon_bg_color: '#e67e22',
      enabled: true,
    }
  ]

  // Configuração padrão dos passos
  const defaultSteps = [
    {
      step: 1,
      title: 'Passo 1',
      description: 'Descrição do passo 1',
      bg_color: '#f3f4f6',
      text_color: '#374151',
      border_color: '#d1d5db',
      icon_check_bg: '#2ecc71',
      icon_check_color: '#ffffff',
    },
    {
      step: 2,
      title: 'Passo 2',
      description: 'Descrição do passo 2',
      bg_color: '#f3f4f6',
      text_color: '#374151',
      border_color: '#d1d5db',
      icon_check_bg: '#2ecc71',
      icon_check_color: '#ffffff',
    },
    {
      step: 3,
      title: 'Passo 3',
      description: 'Descrição do passo 3',
      bg_color: '#f3f4f6',
      text_color: '#374151',
      border_color: '#d1d5db',
      icon_check_bg: '#2ecc71',
      icon_check_color: '#ffffff',
    }
  ]

  // Configuração padrão dos campos do formulário
  // Para produtos digitais: apenas name e email ativos
  // Para produtos físicos: todos os campos ativos
  const getDefaultFormFieldsConfig = (isDigital = false) => {
    if (isDigital) {
      return {
        name: { required: true, visible: true, order: 1, label: 'Nome completo' },
        email: { required: true, visible: true, order: 2, label: 'E-mail' },
        phone: { required: false, visible: false, order: 3, label: 'Telefone' },
        cpf: { required: false, visible: false, order: 4, label: 'CPF' },
        zip_code: { required: false, visible: false, order: 5, label: 'CEP' },
        address: { required: false, visible: false, order: 6, label: 'Endereço' },
        city: { required: false, visible: false, order: 7, label: 'Cidade' },
        state: { required: false, visible: false, order: 8, label: 'Estado' },
        number: { required: false, visible: false, order: 9, label: 'Número' },
        complement: { required: false, visible: false, order: 10, label: 'Complemento' }
      }
    } else {
      return {
        name: { required: true, visible: true, order: 1, label: 'Nome completo' },
        email: { required: true, visible: true, order: 2, label: 'E-mail' },
        phone: { required: true, visible: true, order: 3, label: 'Telefone' },
        cpf: { required: true, visible: true, order: 4, label: 'CPF' },
        zip_code: { required: true, visible: true, order: 5, label: 'CEP' },
        address: { required: true, visible: true, order: 6, label: 'Endereço' },
        city: { required: true, visible: true, order: 7, label: 'Cidade' },
        state: { required: true, visible: true, order: 8, label: 'Estado' },
        number: { required: true, visible: true, order: 9, label: 'Número' },
        complement: { required: true, visible: true, order: 10, label: 'Complemento' }
      }
    }
  }
  
  const defaultFormFieldsConfig = getDefaultFormFieldsConfig(false)

  // Criar formulário baseado se é criação ou edição
  const createForm = () => {
    if (checkout) {
      // Edição - usar dados existentes
      // IMPORTANTE: Preservar EXATAMENTE os valores salvos do banco
      // NÃO mesclar com padrões, NÃO resetar campos, NÃO adicionar campos que não existem
      let formFieldsConfig = {}
      
      if (checkout.form_fields_config && Object.keys(checkout.form_fields_config).length > 0) {
        // Se existe configuração no banco, usar EXATAMENTE como está
        // Criar uma cópia profunda para não modificar o original
        // IMPORTANTE: NÃO adicionar campos, NÃO modificar valores, NÃO adicionar propriedades padrão
        // Usar EXATAMENTE o que está no banco
        formFieldsConfig = JSON.parse(JSON.stringify(checkout.form_fields_config))
        
        // Apenas garantir que campos existentes tenham propriedades mínimas necessárias
        // Mas NUNCA adicionar campos que não existem no banco
        // IMPORTANTE: Preservar EXATAMENTE os valores do banco, apenas adicionar propriedades que realmente faltam
        Object.keys(formFieldsConfig).forEach(key => {
          // Garantir propriedades mínimas apenas se o campo já existe
          if (formFieldsConfig[key] && typeof formFieldsConfig[key] === 'object') {
            // Preservar primeiro todas as propriedades do banco
            const existingField = { ...formFieldsConfig[key] }
            // Criar novo objeto preservando TODAS as propriedades existentes
            // Apenas adicionar propriedades que realmente não existem (undefined ou null)
            const preservedField = { ...existingField }
            
            // Apenas adicionar propriedades que realmente não existem
            // IMPORTANTE: Preservar valores booleanos exatamente como estão (false é um valor válido!)
            if (preservedField.label === undefined || preservedField.label === null) {
              preservedField.label = key
            }
            // visible e required podem ser false, então só adicionar se realmente não existir
            if (!('visible' in preservedField)) {
              preservedField.visible = false
            }
            if (!('required' in preservedField)) {
              preservedField.required = false
            }
            if (preservedField.order === undefined || preservedField.order === null) {
              preservedField.order = 0
            }
            
            formFieldsConfig[key] = preservedField
          }
        })
      } else {
        // Se não existe configuração no banco, usar padrão baseado no tipo de produto
        const product = checkout.product
        const isDigital = product?.type === 'DIGITAL'
        formFieldsConfig = getDefaultFormFieldsConfig(isDigital)
      }

      return useForm({
        product_id: checkout.product_id,
        discount_percentage: checkout.discount_percentage || 0,
        order_bump_ids: checkout.order_bumps?.map(ob => ob.product_id) || [],
        layout: checkout.layout || 'single',
        banner: checkout.banner || null,
        countdown_enabled: checkout.countdown_enabled ?? true,
        countdown_icon: checkout.countdown_icon || '🔥',
        countdown_duration: checkout.countdown_duration || 3600,
        countdown_bg_color: checkout.countdown_bg_color || '#dc2626',
        countdown_text_color: checkout.countdown_text_color || '#ffffff',
        countdown_message: checkout.countdown_message || 'Oferta por tempo limitado!',
        button_primary_color: checkout.button_primary_color || '#2563eb',
        button_secondary_color: checkout.button_secondary_color || '#6b7280',
        button_hover_primary_color: checkout.button_hover_primary_color || '#1d4ed8',
        button_hover_secondary_color: checkout.button_hover_secondary_color || '#4b5563',
        stepped_form_enabled: checkout.stepped_form_enabled || false,
        form_fields_config: formFieldsConfig,
        form_requirements: checkout.form_requirements || ['name', 'email'],
        background_color: checkout.background_color || '#ffffff',
        text_color: checkout.text_color || '#000000',
        steps: checkout.steps || defaultSteps,
        payment_methods: checkout.payment_methods || defaultPaymentMethods,
        
        // Order Bump Customization
        order_bump_enabled: checkout.order_bump_enabled ?? true,
        order_bump_bg_color: checkout.order_bump_bg_color || '#ffffff',
        order_bump_text_color: checkout.order_bump_text_color || '#0f172a',
        order_bump_border_color: checkout.order_bump_border_color || '#fbbf24',
        order_bump_description: checkout.order_bump_description || '',
        order_bump_cta_text: checkout.order_bump_cta_text || 'Quero comprar também!',
        order_bump_cta_bg_color: checkout.order_bump_cta_bg_color || '#10b981',
        order_bump_cta_text_color: checkout.order_bump_cta_text_color || '#ffffff',
        order_bump_recommended_text: checkout.order_bump_recommended_text || '(Recomendado)',
        order_bump_recommended_color: checkout.order_bump_recommended_color || '#fbbf24'
      })
    } else {
      // Criação - usar valores padrão (começar com produto digital por padrão)
      // Quando o produto for selecionado, os campos serão ajustados automaticamente
      return useForm({
        product_id: null,
        discount_percentage: 0,
        order_bump_ids: [],
        layout: 'single',
        banner: null,
        countdown_enabled: true,
        countdown_icon: '🔥',
        countdown_duration: 3600,
        countdown_bg_color: '#dc2626',
        countdown_text_color: '#ffffff',
        countdown_message: 'Oferta por tempo limitado!',
        button_primary_color: '#2563eb',
        button_secondary_color: '#6b7280',
        button_hover_primary_color: '#1d4ed8',
        button_hover_secondary_color: '#4b5563',
        stepped_form_enabled: true,
        form_fields_config: { ...getDefaultFormFieldsConfig(true) }, // Começar como digital por padrão
        form_requirements: ['name', 'email'],
        background_color: '#ffffff',
        text_color: '#000000',
        steps: [...defaultSteps],
        payment_methods: [...defaultPaymentMethods],
        
        // Order Bump Customization
        order_bump_enabled: true,
        order_bump_bg_color: '#ffffff',
        order_bump_text_color: '#0f172a',
        order_bump_border_color: '#fbbf24',
        order_bump_description: '',
        order_bump_cta_text: 'Quero comprar também!',
        order_bump_cta_bg_color: '#10b981',
        order_bump_cta_text_color: '#ffffff',
        order_bump_recommended_text: '(Recomendado)',
        order_bump_recommended_color: '#fbbf24'
      })
    }
  }

  const form = createForm()

  // Estados reativos
  const isSubmitting = ref(false)
  const showDeleteDialog = ref(false)
  const hasUnsavedChanges = ref(false)
  const hoveredPrimary = ref(false)
  const hoveredSecondary = ref(false)
  const newFieldKey = ref('')
  const newFieldLabel = ref('')

  // Funções comuns
  const formatPrice = (price) => {
    if (!price) return '0,00'
    return new Intl.NumberFormat('pt-BR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(price)
  }

  const addCustomField = () => {
    if (newFieldKey.value && newFieldLabel.value) {
      form.form_fields_config[newFieldKey.value] = {
        required: false,
        visible: true,
        label: newFieldLabel.value,
        order: Object.keys(form.form_fields_config).length + 1
      }
      newFieldKey.value = ''
      newFieldLabel.value = ''
    }
  }

  const removeField = (key) => {
    delete form.form_fields_config[key]
  }

  const toggleAllFields = (required) => {
    Object.keys(form.form_fields_config).forEach(key => {
      form.form_fields_config[key].required = required
    })
  }

  const resetAllOrderBumpColors = () => {
    form.order_bump_bg_color = '#ffffff'
    form.order_bump_text_color = '#0f172a'
    form.order_bump_border_color = '#fbbf24'
    form.order_bump_cta_bg_color = '#10b981'
    form.order_bump_cta_text_color = '#ffffff'
    form.order_bump_recommended_color = '#fbbf24'
    toast.success('Cores do Order Bump resetadas com sucesso!')
  }

  const resetBackgroundColors = () => {
    form.background_color = '#ffffff'
    form.text_color = '#000000'
    toast.success('Cores de fundo e texto resetadas com sucesso!')
  }

  const resetButtonColors = () => {
    form.button_primary_color = '#2563eb'
    form.button_secondary_color = '#6b7280'
    form.button_hover_primary_color = '#1d4ed8'
    form.button_hover_secondary_color = '#4b5563'
    toast.success('Cores dos botões resetadas com sucesso!')
  }

  const resetCountdownColors = () => {
    form.countdown_bg_color = '#dc2626'
    form.countdown_text_color = '#ffffff'
    toast.success('Cores da contagem regressiva resetadas com sucesso!')
  }

  const resetStepsColors = () => {
    form.steps.forEach(step => {
      step.bg_color = '#f3f4f6'
      step.text_color = '#374151'
      step.border_color = '#d1d5db'
      step.icon_check_bg = '#2ecc71'
      step.icon_check_color = '#ffffff'
    })
    toast.success('Cores dos passos resetadas com sucesso!')
  }

  // Computed properties
  const availableProducts = computed(() => {
    return window.products?.filter(product => product.id !== form.product_id) || []
  })

  const selectedProduct = computed(() => {
    if (!form.product_id) return null
    return window.products?.find(product => product.id === form.product_id) || null
  })



  // Função para ajustar campos do formulário baseado no tipo de produto
  const adjustFormFieldsByProductType = (product) => {
    if (!product) return
    
    const isDigital = product.type === 'DIGITAL'
    
    // Para produtos digitais: apenas nome e email visíveis e obrigatórios
    // Para produtos físicos: todos os campos visíveis e obrigatórios
    Object.keys(form.form_fields_config).forEach(key => {
      if (isDigital) {
        // Produto digital: apenas nome e email visíveis e obrigatórios
        const isNameOrEmail = (key === 'name' || key === 'email')
        form.form_fields_config[key].visible = isNameOrEmail
        form.form_fields_config[key].required = isNameOrEmail
      } else {
        // Produto físico: todos os campos visíveis e obrigatórios
        form.form_fields_config[key].visible = true
        form.form_fields_config[key].required = true
      }
    })
  }

  // Watchers
  if (checkout) {
    // Apenas para edição - watch para mudanças não salvas
    // IMPORTANTE: Não assistir mudanças em form_fields_config para evitar resetar valores
    watch(() => [
      form.product_id,
      form.discount_percentage,
      form.layout,
      form.countdown_enabled,
      form.button_primary_color,
      form.background_color,
      form.text_color
    ], () => {
      hasUnsavedChanges.value = true
    }, { deep: true })

    // Watch para product_id changes to remove it from order bumps
    watch(() => form.product_id, (newProductId) => {
      form.order_bump_ids = form.order_bump_ids.filter(id => id != newProductId)
    })
    
    // Watch específico para form_fields_config - preservar valores ao editar
    // Não resetar quando outros campos mudam
    watch(() => form.form_fields_config, (newConfig) => {
      hasUnsavedChanges.value = true
    }, { deep: true })
  } else {
    // Apenas para criação - watch para ajustar campos quando produto é selecionado
    watch(() => form.product_id, (newProductId) => {
      if (newProductId) {
        const product = window.products?.find(p => p.id === newProductId)
        if (product) {
          adjustFormFieldsByProductType(product)
        }
      }
    })
  }

  return {
    // Dados
    form,
    countdownDurations,
    defaultPaymentMethods,
    defaultSteps,
    defaultFormFieldsConfig: getDefaultFormFieldsConfig(false),
    
    // Estados
    isSubmitting,
    showDeleteDialog,
    hasUnsavedChanges,
    hoveredPrimary,
    hoveredSecondary,
    newFieldKey,
    newFieldLabel,
    
    // Funções
    formatPrice,
    addCustomField,
    removeField,
    toggleAllFields,
    resetAllOrderBumpColors,
    resetBackgroundColors,
    resetButtonColors,
    resetCountdownColors,
    resetStepsColors,
    
    // Computed
    availableProducts,
    selectedProduct
  }
}
