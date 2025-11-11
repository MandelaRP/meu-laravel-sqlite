<!-- eslint-disable vue/block-lang -->
<script setup>
import { Check, User, MapPin, FileText, Upload, Loader2, Building2 } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { ref, watch } from 'vue'
import { Label } from '@/components/ui/label'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { vMaska } from "maska/vue"
import { useToast } from '@/components/ui/toast/use-toast'
import { router, useForm } from '@inertiajs/vue3'
import LogoDark from '@/pages/Onboarding/LogoDark.vue'
import InputError from '@/components/InputError.vue'


const { toast } = useToast()
const isLoadingCNPJ = ref(false)
const isLoadingCEP = ref(false)
const showAddressFields = ref(false)

const steps = [
    { step: 1, title: 'Pessoal', icon: User },
    { step: 2, title: 'Endereço', icon: MapPin },
    { step: 3, title: 'Documentos', icon: FileText },
]

const currentStep = ref(1)
const completedSteps = ref([])

const form = useForm({
    person_type: 'pf',
    full_name: '',
    phone: '',
    document: '',
    social_reason: '',
    average_revenue: '',
    average_ticket: '',
    products: '',
    address: '',
    number: '',
    city: '',
    state: '',
    zip_code: '',
    social_contract: null,
    rg_cnh_frente: null,
    rg_cnh_verso: null,
    selfie: null,
})

const isStepCompleted = (step) => completedSteps.value.includes(step)
const isStepActive = (step) => currentStep.value === step

const markStepAsCompleted = (step) => {
    if (!completedSteps.value.includes(step)) {
        completedSteps.value.push(step)
    }
}

const removeLastCompletedStep = () => {
    completedSteps.value = completedSteps.value.filter(step => step < currentStep.value)
}

const fetchCNPJData = async (cnpj) => {
    // try {
    //     isLoadingCNPJ.value = true
    //     const response = await fetch(`https://api.cnpja.com/office/${cnpj.replace(/\D/g, '')}`, {
    //         headers: {
    //             'Authorization': '51016a67-2a49-437d-b9da-ad24b8d4d5f4-83166513-e969-4b96-beb7-78615195fc9f'
    //         }
    //     })

    //     if (!response.ok) throw new Error('Erro ao buscar dados do CNPJ')

    //     const data = await response.json()
    //     form.social_reason = data.company.name
    //     form.address = data.address.street
    //     form.number = data.address.number
    //     form.city = data.address.city
    //     form.state = data.address.state
    //     form.zip_code = data.address.zip_code

    //     toast({
    //         title: "CNPJ encontrado",
    //         description: "Dados preenchidos automaticamente",
    //     })
    // } catch (error) {
    //     toast({
    //         title: "Erro ao buscar CNPJ",
    //         description: "Verifique o número informado",
    //         variant: "destructive",
    //     })
    // } finally {
    //     isLoadingCNPJ.value = false
    // }
}

const fetchCEPData = async (cep) => {
    try {
        isLoadingCEP.value = true
        const cleanCEP = cep.replace(/\D/g, '')
        
        if (cleanCEP.length !== 8) {
            return
        }

        // Limpar campos antes de buscar
        form.address = ''
        form.city = ''
        form.state = ''
        showAddressFields.value = false

        const response = await fetch(`https://viacep.com.br/ws/${cleanCEP}/json/`)
        const data = await response.json()

        if (data.erro) {
            toast({
                title: "CEP não encontrado",
                description: "Verifique o CEP informado",
                variant: "destructive",
            })
            showAddressFields.value = false
            return
        }

        // Preencher campos automaticamente
        form.address = data.logradouro || ''
        form.city = data.localidade || ''
        form.state = data.uf || ''
        form.zip_code = cep
        showAddressFields.value = true

        toast({
            title: "CEP encontrado",
            description: "Endereço preenchido automaticamente",
        })
    } catch (error) {
        console.error('Erro ao buscar CEP:', error)
        toast({
            title: "Erro ao buscar CEP",
            description: "Não foi possível encontrar o endereço",
            variant: "destructive",
        })
        showAddressFields.value = false
    } finally {
        isLoadingCEP.value = false
    }
}

watch(() => form.document, (newValue) => {
    if (form.person_type === 'pj' && newValue.length === 18) {
        fetchCNPJData(newValue)
    }
})

watch(() => form.person_type, (newValue) => {
    console.log('Watch person_type:', newValue)
    // Limpa o documento quando mudar o tipo de pessoa
    form.document = ''

    if (newValue === 'pf') {
        // Limpa campos específicos de PJ
        form.social_reason = ''
        form.social_contract = null
    }
})

watch(() => form.zip_code, (newValue) => {
    const cleanCEP = newValue?.replace(/\D/g, '') || ''
    if (cleanCEP.length === 8) {
        fetchCEPData(newValue)
    } else {
        // Limpar campos se CEP for inválido
        if (cleanCEP.length > 0 && cleanCEP.length < 8) {
            form.address = ''
            form.city = ''
            form.state = ''
            showAddressFields.value = false
        }
    }
})

watch([
    () => form.address,
    () => form.city,
    () => form.state
], ([address, city, state]) => {
    showAddressFields.value = !!(address || city || state)
})

const validateFileType = (file, allowedTypes) => {
    if (!file) return false
    return allowedTypes.includes(file.type)
}

const validateFileSize = (file) => {
    const maxSize = 5 * 1024 * 1024
    return file.size <= maxSize
}

const handleFileUpload = (event, field) => {
    const file = event.target.files[0]
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf']

    if (!file) return

    if (!validateFileType(file, allowedTypes)) {
        toast({
            title: "Tipo de arquivo inválido",
            description: "Envie apenas PDF, JPG ou PNG",
            variant: "destructive",
        })
        event.target.value = ''
        return
    }

    if (!validateFileSize(file)) {
        toast({
            title: "Arquivo muito grande",
            description: "Máximo 5MB",
            variant: "destructive",
        })
        event.target.value = ''
        return
    }

    // Atualiza o form com o arquivo
    form[field] = file

    toast({
        title: "Arquivo selecionado",
        description: file.name,
    })
}

const nextStep = () => {
    if (currentStep.value < steps.length) {
        markStepAsCompleted(currentStep.value)
        currentStep.value++
    }
}

const previousStep = () => {
    if (currentStep.value > 1) {
        removeLastCompletedStep()
        currentStep.value--
    }
}

const onSubmit = () => {
    if (currentStep.value < steps.length) {
        nextStep()
        return
    }

    // Criar um FormData para enviar os arquivos
    const formData = new FormData()

    // Adicionar todos os campos do formulário
    Object.keys(form).forEach(key => {
        // Se for um arquivo, adiciona diretamente
        if (key === 'social_contract' || key === 'rg_cnh_frente' || key === 'rg_cnh_verso' || key === 'selfie') {
            if (form[key]) {
                formData.append(key, form[key])
            }
        } else {
            // Para outros campos, adiciona o valor normalmente
            formData.append(key, form[key])
        }
    })

    form.post(route('onboarding.store'), {
        forceFormData: true,
        data: formData,
        onSuccess: () => {
            // Marcar que o cadastro foi enviado para análise
            localStorage.setItem('onboarding_submitted', 'true')
            toast({
                title: "Cadastro concluído",
                description: "Bem-vindo ao LuckyPay",
            })
            router.visit(route('dashboard'))
        },
        onError: (errors) => {
            console.error('Erros no envio:', errors)
            toast({
                title: "Erro ao cadastrar",
                description: "Verifique os campos",
                variant: "destructive",
            })
        }
    })
}

</script>

<template>


    <div class="min-h-screen bg-background flex items-center justify-center p-4">
        <div class="w-full max-w-lg">
            <!-- Header -->
            <div class="text-center mb-12 space-y-4">

                <LogoDark />
                <p class="text-muted-foreground">Complete seu cadastro em {{ steps.length }} etapas simples</p>
            </div>

            <!-- Progress -->
            <!-- <div class="mb-8">
                <div class="flex justify-between text-xs text-muted-foreground mb-2">
                    <span>Etapa {{ currentStep }} de {{ steps.length }}</span>
                    <span>{{ Math.round((completedSteps.length / steps.length) * 100) }}%</span>
                </div>
                <div class="w-full bg-muted rounded-full h-1">
                    <div class="bg-foreground h-1 rounded-full transition-all duration-300" 
                         :style="{ width: (completedSteps.length / steps.length) * 100 + '%' }"></div>
                </div>
            </div> -->



            <!-- Steps -->
            <div class="flex justify-center mb-12">
                <div class="flex items-center space-x-4">
                    <div v-for="(step, index) in steps" :key="step.step" class="flex items-center">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm transition-all"
                                :class="[
                                    isStepCompleted(step.step) ? 'bg-foreground text-background' :
                                        isStepActive(step.step) ? 'bg-muted text-foreground border-2 border-foreground' :
                                            'bg-muted text-muted-foreground'
                                ]">
                                <Check v-if="isStepCompleted(step.step)" class="w-4 h-4" />
                                <component v-else :is="step.icon" class="w-4 h-4" />
                            </div>
                            <span class="text-xs mt-2 font-medium" :class="[
                                isStepCompleted(step.step) || isStepActive(step.step) ? 'text-foreground' : 'text-muted-foreground'
                            ]">
                                {{ step.title }}
                            </span>
                        </div>

                        <div v-if="index < steps.length - 1" class="w-8 h-px mx-2 transition-colors"
                            :class="isStepCompleted(step.step) ? 'bg-foreground' : 'bg-border'">
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div class="mb-8">
                <ul>
                    <li class="text-red-500" v-for="error in form.errors" :key="error">
                        {{ error }}
                    </li>
                </ul>
            </div> -->

            <!-- Content -->
            <div class="space-y-8">
                <!-- Step 1: Dados Pessoais -->
                <div v-if="currentStep === 1" class="space-y-6">
                    <!-- <div class="text-center mb-8">
                        <h2 class="text-xl font-medium mb-2 text-foreground">Dados pessoais</h2>
                        <p class="text-sm text-muted-foreground">Suas informações básicas</p>
                    </div> -->



                    <!-- Tipo de Pessoa -->
                    <div class="space-y-3">
                        <Label class="text-sm font-medium dark:text-foreground sr-only">Tipo de pessoa</Label>
                        <RadioGroup v-model="form.person_type" class="grid grid-cols-2 gap-4">
                            <div>
                                <RadioGroupItem value="pf" id="pf" class="peer sr-only" />
                                <Label for="pf" :class="[
                                    'flex flex-col items-center justify-center p-4 h-24 border-2 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-muted transition-all relative',
                                    form.person_type === 'pf' ? 'border-primary bg-primary/5' : 'border-input'
                                ]">
                                    <div v-show="form.person_type === 'pf'"
                                        class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-[#10B981] flex items-center justify-center">
                                        <Check class="w-4 h-4 text-white" />
                                    </div>
                                    <User :class="form.person_type === 'pf' ? 'text-primary' : 'text-muted-foreground'"
                                        class="w-6 h-6 mb-2" />
                                    <span :class="form.person_type === 'pf' ? 'text-primary' : 'text-foreground'"
                                        class="text-sm font-medium">Pessoa Física</span>
                                    <span class="text-xs text-muted-foreground mt-1">CPF</span>
                                </Label>
                            </div>
                            <div>
                                <RadioGroupItem value="pj" id="pj" class="peer sr-only" />
                                <Label for="pj" :class="[
                                    'flex flex-col items-center justify-center p-4 h-24 border-2 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-muted transition-all relative',
                                    form.person_type === 'pj' ? 'border-primary bg-primary/5' : 'border-input'
                                ]">
                                    <div v-show="form.person_type === 'pj'"
                                        class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-[#10B981] flex items-center justify-center">
                                        <Check class="w-4 h-4 text-white" />
                                    </div>
                                    <Building2
                                        :class="form.person_type === 'pj' ? 'text-primary' : 'text-muted-foreground'"
                                        class="w-6 h-6 mb-2" />
                                    <span :class="form.person_type === 'pj' ? 'text-primary' : 'text-foreground'"
                                        class="text-sm font-medium">Pessoa Jurídica</span>
                                    <span class="text-xs text-muted-foreground mt-1">CNPJ</span>
                                </Label>
                            </div>
                        </RadioGroup>
                    </div>

                    <!-- Nome -->
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-foreground">
                            {{ form.person_type === 'pf' ? 'Nome completo' : 'Nome do responsável' }}
                        </Label>
                        <Input v-model="form.full_name" class="h-11" placeholder="Digite seu nome" />
                        <InputError :message="form.errors.full_name" />
                    </div>

                    <!-- Telefone e Documento -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-foreground">Telefone</Label>
                            <Input v-model="form.phone" class="h-11" placeholder="(00) 00000-0000"
                                v-maska="'(##) #####-####'" />
                            <InputError :message="form.errors.phone" />
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-foreground">{{ form.person_type === 'pf' ? 'CPF' :
                                'CNPJ' }}</Label>
                            <div class="relative">
                                <Input v-model="form.document" class="h-11"
                                    :placeholder="form.person_type === 'pf' ? '000.000.000-00' : '00.000.000/0000-00'"
                                    v-maska="form.person_type === 'pf' ? '###.###.###-##' : '##.###.###/####-##'" />
                                <Loader2 v-if="form.person_type === 'pj' && isLoadingCNPJ"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 animate-spin text-muted-foreground" />
                            </div>
                            <InputError :message="form.errors.document" />
                        </div>
                    </div>

                    <!-- Razão Social (PJ) -->
                    <div v-if="form.person_type === 'pj'" class="space-y-2">
                        <Label class="text-sm font-medium text-foreground">Razão social</Label>
                        <Input v-model="form.social_reason" class="h-11" placeholder="Nome da empresa" />
                        <InputError :message="form.errors.social_reason" />
                    </div>

                    <!-- Faturamento -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-foreground">Faturamento mensal</Label>
                            <Input v-model="form.average_revenue" class="h-11" placeholder="R$ 0,00" />
                            <InputError :message="form.errors.average_revenue" />
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-foreground">Ticket médio</Label>
                            <Input v-model="form.average_ticket" class="h-11" placeholder="R$ 0,00" />
                            <InputError :message="form.errors.average_ticket" />
                        </div>
                    </div>

                    <!-- Produtos -->
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-foreground">Produtos</Label>
                        <Input v-model="form.products" class="h-11" placeholder="Ex: Roupas, Calçados..." />
                        <InputError :message="form.errors.products" />
                    </div>
                </div>

                <!-- Step 2: Endereço -->
                <div v-if="currentStep === 2" class="space-y-6">
                    <!-- <div class="text-center mb-8">
                        <h2 class="text-xl font-medium mb-2 text-foreground">Endereço</h2>
                        <p class="text-sm text-muted-foreground">Onde você está localizado</p>
                    </div> -->

                    <!-- CEP -->
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-foreground">CEP</Label>
                        <div class="relative">
                            <Input v-model="form.zip_code" class="h-11" placeholder="00000-000" maxlength="9" v-maska
                                data-maska="#####-###" />
                            <Loader2 v-if="isLoadingCEP"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 animate-spin text-muted-foreground" />
                        </div>
                        <p v-if="!showAddressFields" class="text-xs text-muted-foreground">
                            Digite o CEP para preencher automaticamente
                        </p>
                        <InputError :message="form.errors.zip_code" />
                    </div>

                    <!-- Endereço completo -->
                    <div v-if="showAddressFields" class="space-y-4">
                        <div class="grid grid-cols-4 gap-3">
                            <div class="col-span-3 space-y-2">
                                <Label class="text-sm font-medium text-foreground">Endereço</Label>
                                <Input v-model="form.address" class="h-11" placeholder="Rua, Avenida..." />
                                <InputError :message="form.errors.address" />
                            </div>

                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-foreground">Número</Label>
                                <Input v-model="form.number" class="h-11" placeholder="123" />
                                <InputError :message="form.errors.number" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-foreground">Cidade</Label>
                                <Input v-model="form.city" class="h-11" placeholder="Cidade" />
                                <InputError :message="form.errors.city" />
                            </div>

                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-foreground">Estado</Label>
                                <Input v-model="form.state" class="h-11" placeholder="UF" />
                                <InputError :message="form.errors.state" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Documentos -->
                <div v-if="currentStep === 3" class="space-y-6">
                    <!-- <div class="text-center mb-8">
                        <h2 class="text-xl font-medium mb-2 text-foreground">Documentos</h2>
                        <p class="text-sm text-muted-foreground">Para verificar sua identidade</p>
                    </div> -->

                    <!-- Contrato Social (PJ) -->
                    <div v-if="form.person_type === 'pj'" class="space-y-2">
                        <Label class="text-sm font-medium text-foreground">Contrato social</Label>
                        <input type="file" class="hidden" ref="contratoSocialInput" accept=".pdf,.jpg,.jpeg,.png"
                            @change="e => handleFileUpload(e, 'social_contract')" />
                        <div class="border-2 border-dashed border-border rounded-lg p-6 text-center cursor-pointer hover:border-muted-foreground transition-colors bg-card"
                            @click="$refs.contratoSocialInput.click()">
                            <Upload class="w-6 h-6 mx-auto mb-2 text-muted-foreground" />
                            <div v-if="!form.social_contract">
                                <p class="text-sm font-medium text-foreground">Clique para enviar</p>
                                <p class="text-xs text-muted-foreground mt-1">PDF, JPG ou PNG (máx. 5MB)</p>
                            </div>
                            <div v-else class="flex items-center justify-center gap-2">
                                <Check class="w-4 h-4 text-green-600" />
                                <span class="text-sm font-medium text-green-600">{{ form.social_contract.name }}</span>
                            </div>
                        </div>
                        <InputError :message="form.errors.social_contract" />
                    </div>
    

                    <!-- RG/CNH Frente -->
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-foreground">
                            {{ form.person_type === 'pf' ? 'RG/CNH (frente)' : 'Documento responsável (frente)' }}
                        </Label>
                        <input type="file" class="hidden" ref="rgFrenteInput" accept=".jpg,.jpeg,.png"
                            @change="e => handleFileUpload(e, 'rg_cnh_frente')" />
                        <div class="border-2 border-dashed border-border rounded-lg p-6 text-center cursor-pointer hover:border-muted-foreground transition-colors bg-card"
                            @click="$refs.rgFrenteInput.click()">
                            <Upload class="w-6 h-6 mx-auto mb-2 text-muted-foreground" />
                            <div v-if="!form.rg_cnh_frente">
                                <p class="text-sm font-medium text-foreground">Clique para enviar</p>
                                <p class="text-xs text-muted-foreground mt-1">JPG ou PNG (máx. 5MB)</p>
                            </div>
                            <div v-else class="flex items-center justify-center gap-2">
                                <Check class="w-4 h-4 text-green-600" />
                                <span class="text-sm font-medium text-green-600">{{ form.rg_cnh_frente.name }}</span>
                            </div>
                        </div>
                        <InputError :message="form.errors.rg_cnh_frente" />
                    </div>

                    <!-- RG/CNH Verso -->
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-foreground">
                            {{ form.person_type === 'pf' ? 'RG/CNH (verso)' : 'Documento responsável (verso)' }}
                        </Label>
                        <input type="file" class="hidden" ref="rgVersoInput" accept=".jpg,.jpeg,.png"
                            @change="e => handleFileUpload(e, 'rg_cnh_verso')" />
                        <div class="border-2 border-dashed border-border rounded-lg p-6 text-center cursor-pointer hover:border-muted-foreground transition-colors bg-card"
                            @click="$refs.rgVersoInput.click()">
                            <Upload class="w-6 h-6 mx-auto mb-2 text-muted-foreground" />
                            <div v-if="!form.rg_cnh_verso">
                                <p class="text-sm font-medium text-foreground">Clique para enviar</p>
                                <p class="text-xs text-muted-foreground mt-1">JPG ou PNG (máx. 5MB)</p>
                            </div>
                            <div v-else class="flex items-center justify-center gap-2">
                                <Check class="w-4 h-4 text-green-600" />
                                <span class="text-sm font-medium text-green-600">{{ form.rg_cnh_verso.name }}</span>
                            </div>
                        </div>
                        <InputError :message="form.errors.rg_cnh_verso" />
                    </div>

                    <!-- Selfie -->
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-foreground">
                            {{ form.person_type === 'pf' ? 'Selfie com documento' : 'Selfie do responsável' }}
                        </Label>
                        <input type="file" class="hidden" ref="selfieInput" accept=".jpg,.jpeg,.png"
                            @change="e => handleFileUpload(e, 'selfie')" />
                        <div class="border-2 border-dashed border-border rounded-lg p-6 text-center cursor-pointer hover:border-muted-foreground transition-colors bg-card"
                            @click="$refs.selfieInput.click()">
                            <Upload class="w-6 h-6 mx-auto mb-2 text-muted-foreground" />
                            <div v-if="!form.selfie">
                                <p class="text-sm font-medium text-foreground">Clique para enviar</p>
                                <p class="text-xs text-muted-foreground mt-1">JPG ou PNG (máx. 5MB)</p>
                            </div>
                            <div v-else class="flex items-center justify-center gap-2">
                                <Check class="w-4 h-4 text-green-600" />
                                <span class="text-sm font-medium text-green-600">{{ form.selfie.name }}</span>
                            </div>
                        </div>
                        <InputError :message="form.errors.selfie" />
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex justify-between items-center pt-6">
                    <Button type="button" variant="ghost" :disabled="currentStep === 1" @click="previousStep"
                        class="text-muted-foreground hover:text-foreground">
                        Voltar
                    </Button>

                    <Button type="button" @click="onSubmit" :disabled="form.processing"
                        class="bg-foreground hover:bg-foreground/90 text-background px-8">
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin mr-2" />
                        {{ currentStep === steps.length ? 'Concluir' : 'Continuar' }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.peer:checked~label {
    @apply border-foreground bg-muted/50;
}

.shadow-3xl {
    box-shadow: 0 35px 60px -12px rgba(0, 0, 0, 0.25);
}
</style>