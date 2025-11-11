<script setup>
import { reactive, computed, ref, watch } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Loader2, CheckCircle, AlertCircle, MapPin, User, Mail, Phone, CreditCard, PinIcon, PlusIcon } from 'lucide-vue-next'

const props = defineProps({
    form: Object,
    showSubmitButton: {
        type: Boolean,
        default: true
    }
})

const emit = defineEmits(['form-submitted', 'form-data-changed', 'submit-clicked'])

// Ordenar campos pela propriedade "order" e filtrar apenas os visíveis
const orderedFields = computed(() => {
    return Object.entries(props.form)
        .map(([key, value]) => ({ key, ...value }))
        .filter(field => field.visible !== false) // Filtrar campos não visíveis
        .sort((a, b) => (a.order || 0) - (b.order || 0))
})

// Garantir que props.form seja sempre um objeto válido
if (!props.form || typeof props.form !== 'object') {
    console.warn('Form.vue: props.form não é um objeto válido', props.form)
}

// Inicializar os dados do formulário
const formData = reactive(
    Object.keys(props.form || {}).reduce((acc, key) => {
        acc[key] = ''
        return acc
    }, {})
)

// Estados do formulário
const isLoading = ref(false)
const isSubmitting = ref(false)
const cepError = ref('')
const formErrors = reactive({})
const showAddressFields = ref(false)

// Garantir que formErrors seja sempre um objeto válido
watch(() => props.form, (newForm) => {
    if (!newForm || typeof newForm !== 'object') return
    
    // Inicializar formErrors para todos os campos
    Object.keys(newForm).forEach(key => {
        // Garantir que formErrors[key] seja sempre uma string, nunca boolean ou outro tipo
        if (!(key in formErrors) || typeof formErrors[key] !== 'string') {
            formErrors[key] = ''
        }
    })
}, { immediate: true, deep: true })

// Função para determinar o tipo do input
const getInputType = (key) => {
    if (key.includes('email')) return 'email'
    if (key.includes('cpf') || key.includes('zip') || key.includes('phone')) return 'text'
    return 'text'
}

// Função para obter ícone baseado no tipo do campo
const getFieldIcon = (key) => {
    if (key.includes('name') || key.includes('nome')) return User
    if (key.includes('email')) return Mail
    if (key.includes('phone') || key.includes('telefone')) return Phone
    if (key.includes('zip') || key.includes('address') || key.includes('endereco')) return MapPin
    if (key.includes('card') || key.includes('payment')) return CreditCard
    if (key.includes('number')) return PinIcon
    if (key.includes('complement')) return PlusIcon
    return User
}

// Função para aplicar máscara no campo
const applyMask = (value, key) => {
    if (key.includes('phone') || key.includes('telefone')) {
        return value.replace(/\D/g, '').replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3')
    }
    if (key.includes('cpf')) {
        return value.replace(/\D/g, '').replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
    }
    if (key.includes('zip') || key.includes('cep')) {
        return value.replace(/\D/g, '').replace(/(\d{5})(\d{3})/, '$1-$2')
    }
    return value
}

// Função para verificar se é campo de endereço
const isAddressField = (key) => {
    return ['address', 'city', 'state', 'number', 'complement'].includes(key)
}

// Função para verificar se deve mostrar o campo
const shouldShowField = (field) => {
    // Verificar se o campo está visível
    if (field.visible === false) {
        return false
    }

    // Sempre mostrar campos que não são de endereço
    if (!isAddressField(field.key)) {
        return true
    }

    // Mostrar campos de endereço apenas se o CEP foi preenchido e validado
    return showAddressFields.value
}

// Buscar endereço automaticamente ao preencher CEP
const fetchAddress = async () => {
    const cep = formData.zip_code.replace(/\D/g, '')

    // Resetar erro e limpar campos de endereço
    cepError.value = ''
    formErrors.zip_code = ''
    formData.address = ''
    formData.number = ''
    formData.complement = ''
    formData.city = ''
    formData.state = ''
    showAddressFields.value = false

    if (cep.length !== 8) {
        return
    }

    isLoading.value = true
    try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
        const data = await response.json()

        if (data.erro) {
            cepError.value = 'CEP não encontrado.'
            formErrors.zip_code = 'CEP não encontrado.'
            showAddressFields.value = false
        } else {
            formData.address = data.logradouro || ''
            formData.city = data.localidade || ''
            formData.state = data.uf || ''
            cepError.value = ''
            formErrors.zip_code = ''
            showAddressFields.value = true
        }
    } catch (error) {
        console.log(error)
        cepError.value = 'Erro ao buscar o CEP.'
        formErrors.zip_code = 'Erro ao buscar o CEP.'
        showAddressFields.value = false
    } finally {
        isLoading.value = false
    }
}

// Assistir mudanças no campo de CEP
watch(() => formData.zip_code, (newVal) => {
    if (newVal && newVal.replace(/\D/g, '').length === 8) {
        fetchAddress()
    } else {
        cepError.value = ''
        // Garantir que formErrors.zip_code existe antes de atribuir
        if (formErrors && typeof formErrors === 'object') {
            formErrors.zip_code = ''
        }
        showAddressFields.value = false
    }
})

// Assistir mudanças nos dados do formulário
watch(formData, (newData) => {
    emit('form-data-changed', {
        formData: newData,
        hasErrors: false, // Não validar formulários
        timestamp: new Date().toISOString()
    })
}, { deep: true })

// Flag para controlar se deve mostrar erros (desabilitado - formulário é opcional)
const shouldShowErrors = ref(false)

// Validar campo individual
const validateField = (key, value) => {
    // Garantir que formErrors[key] existe e é uma string
    if (!formErrors || typeof formErrors !== 'object') {
        return true
    }
    
    // Garantir que a chave existe em formErrors
    if (!(key in formErrors) || typeof formErrors[key] !== 'string') {
        formErrors[key] = ''
    }
    
    // Só validar se o campo é obrigatório
    const field = orderedFields.value.find(f => f.key === key)
    if (!field || !field.required) {
        // Limpar erro apenas se shouldShowErrors estiver ativo
        if (shouldShowErrors.value) {
            formErrors[key] = ''
        }
        return true
    }

    // Só mostrar erro se o usuário tentou enviar o formulário
    // NÃO mostrar erros no blur inicial, apenas após tentativa de submit
    if (!shouldShowErrors.value) {
        // Não limpar nem definir erros se não deve mostrar
        return true
    }

    if (!value || (typeof value === 'string' && value.trim() === '')) {
        formErrors[key] = 'Este campo é obrigatório.'
        return false
    }

    if (key.includes('email') && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        formErrors[key] = 'Email inválido.'
        return false
    }

    if (key.includes('phone') && value.replace(/\D/g, '').length < 10) {
        formErrors[key] = 'Telefone inválido.'
        return false
    }

    formErrors[key] = ''
    return true
}

// Função para validar formulário (sem mostrar erros)
const checkFormValidity = () => {
    let isValid = true
    orderedFields.value.forEach(field => {
        if (field.required) {
            const value = formData[field.key]
            if (!value || value.trim() === '') {
                isValid = false
            } else if (field.key.includes('email') && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                isValid = false
            } else if (field.key.includes('phone') && value.replace(/\D/g, '').length < 10) {
                isValid = false
            }
        }
    })
    return isValid
}

// Função para validar formulário e mostrar erros
const validateForm = () => {
    // Ativar flag de mostrar erros
    shouldShowErrors.value = true
    
    let isValid = true
    orderedFields.value.forEach(field => {
        if (field.required && !validateField(field.key, formData[field.key])) {
            isValid = false
        }
    })
    return isValid
}

// Submissão do formulário
const handleSubmit = async () => {
    // Ativar flag de mostrar erros ANTES de validar
    shouldShowErrors.value = true
    
    // Emitir evento de clique no submit
    emit('submit-clicked', {
        formData,
        isValid: validateForm(),
        timestamp: new Date().toISOString()
    })

    isSubmitting.value = true

    // Validar apenas campos obrigatórios
    const isValid = validateForm()

    if (!isValid) {
        isSubmitting.value = false
        return
    }

    try {
        // Simular envio
        await new Promise(resolve => setTimeout(resolve, 2000))
        console.log('Dados enviados:', formData)

        // Emitir dados do formulário para o componente pai
        emit('form-submitted', {
            formData,
            isValid,
            timestamp: new Date().toISOString()
        })

        // Aqui você pode adicionar a lógica real de envio
    } catch (error) {
        console.error('Erro ao enviar formulário:', error)

        // Emitir erro para o componente pai
        emit('form-submitted', {
            formData,
            isValid: false,
            error: error.message,
            timestamp: new Date().toISOString()
        })
    } finally {
        isSubmitting.value = false
    }
}

// Expor funções e dados para o componente pai
defineExpose({
    formData,
    validateForm,
    checkFormValidity,
    isSubmitting,
    formErrors,
    submitForm: handleSubmit
})
</script>

<template>
 
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="field in orderedFields" :key="field.key" v-show="shouldShowField(field)"
                        class="relative">
                        <Label :for="field.key" class="text-sm font-medium mb-2 block" :style="{ color: 'inherit' }">
                            {{ field.label }}
                            <span v-if="field.required === true" class="text-red-500 ml-1">*</span>
                        </Label>

                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <component :is="getFieldIcon(field.key)" class="h-5 w-5 opacity-60" />
                            </div>

                            <Input v-model="formData[field.key]" :type="getInputType(field.key)" :id="field.key"
                                :disabled="field.key === 'zip_code' && isLoading"
                                :placeholder="field.placeholder || `Digite seu ${field.label.toLowerCase()}`"
                                class="pl-10 h-12 transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-xl bg-white border-gray-200"
                                :class="{
                                    'border-red-500 focus:ring-red-500 focus:border-red-500': shouldShowErrors && (formErrors[field.key] || (field.key === 'zip_code' && cepError)),
                                    'border-green-500 focus:ring-green-500 focus:border-green-500': formData[field.key] && (!shouldShowErrors || !formErrors[field.key]),
                                    'opacity-50 cursor-not-allowed bg-gray-50': field.key === 'zip_code' && isLoading
                                }" @input="(e) => {
                                    try {
                                        if (field.key.includes('phone') || field.key.includes('cpf') || field.key.includes('zip')) {
                                            e.target.value = applyMask(e.target.value, field.key)
                                        }
                                        // Validar apenas se já tentou enviar
                                        if (shouldShowErrors && typeof shouldShowErrors === 'object' && shouldShowErrors.value) {
                                            validateField(field.key, e.target.value)
                                        }
                                    } catch (error) {
                                        console.error('Erro no input handler:', error)
                                    }
                                }" @blur="() => {
                                    try {
                                        // Validar apenas se já tentou enviar
                                        if (shouldShowErrors && typeof shouldShowErrors === 'object' && shouldShowErrors.value) {
                                            validateField(field.key, formData[field.key])
                                        }
                                    } catch (error) {
                                        console.error('Erro no blur handler:', error)
                                    }
                                }" />

                            <!-- Loading indicator for CEP -->
                            <div v-if="field.key === 'zip_code' && isLoading"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <div class="flex items-center gap-2">
                                    <Loader2 class="h-5 w-5 text-blue-500 animate-spin" />
                                    <span class="text-xs text-blue-500 font-medium">Buscando...</span>
                                </div>
                            </div>

                            <!-- Success indicator -->
                            <div v-else-if="formData[field.key] && !formErrors[field.key]"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <CheckCircle class="h-5 w-5 text-green-500" />
                            </div>

                            <!-- Error indicator -->
                            <div v-else-if="shouldShowErrors && formErrors[field.key]"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <AlertCircle class="h-5 w-5 text-red-500" />
                            </div>
                        </div>

                        <!-- Error messages -->
                        <p v-if="shouldShowErrors && (formErrors[field.key] || (field.key === 'zip_code' && cepError))"
                            class="text-red-500 text-sm mt-1 flex items-center gap-1">
                            <AlertCircle class="h-4 w-4" />
                            {{ formErrors[field.key] || cepError }}
                        </p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div v-if="showSubmitButton">
                    <Button type="submit" :disabled="isSubmitting"
                        class="w-full h-12 text-lg font-semibold transition-all duration-200" :class="{
                            'bg-blue-600 hover:bg-blue-700': !isSubmitting,
                            'bg-gray-400 cursor-not-allowed': isSubmitting
                        }">
                        <Loader2 v-if="isSubmitting" class="mr-2 h-5 w-5 animate-spin" />
                        {{ isSubmitting ? 'Processando...' : 'Finalizar Compra' }}
                    </Button>
                </div>

                <!-- Slot para botão customizado -->
                <slot name="submit-button" :form-data="formData" :is-valid="checkFormValidity()"
                    :is-submitting="isSubmitting" :handle-submit="handleSubmit">
                    <!-- Fallback: botão padrão se nenhum slot for fornecido -->
                </slot>
            </form>

</template>

<style scoped>
/* Custom scrollbar for better UX */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Smooth transitions */
* {
    transition: all 0.2s ease-in-out;
}

/* Focus styles */
input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Error state animations */
@keyframes shake {

    0%,
    100% {
        transform: translateX(0);
    }

    25% {
        transform: translateX(-5px);
    }

    75% {
        transform: translateX(5px);
    }
}

.border-red-500 {
    animation: shake 0.5s ease-in-out;
}
</style>
