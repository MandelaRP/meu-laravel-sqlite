<script setup>
import { ref, watch, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Upload, X } from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: [File, String, null],
        default: null
    },
    preview: {
        type: String,
        default: null
    },
    label: {
        type: String,
        default: 'Imagem'
    },
    placeholder: {
        type: String,
        default: 'Clique para fazer upload ou arraste uma imagem'
    },
    accept: {
        type: String,
        default: 'image/*'
    },
    maxSize: {
        type: Number,
        default: 2048 // 2MB
    }
});

const emit = defineEmits(['update:modelValue', 'update:preview']);

const fileInput = ref(null);
const imagePreview = ref(null);

// Computed para determinar se temos uma imagem (seja arquivo ou URL)
const hasImage = computed(() => {
    return imagePreview.value !== null;
});

// Função para gerar preview de URL existente
const generateUrlPreview = (url) => {
    if (url && typeof url === 'string') {
        // Se é uma URL completa, usar diretamente
        if (url.startsWith('http://') || url.startsWith('https://')) {
            return url;
        }
        // Se é um caminho relativo, adicionar /storage/
        if (url.startsWith('/storage/')) {
            return url;
        }
        // Se é apenas o nome do arquivo, adicionar /storage/
        return `/storage/${url}`;
    }
    return null;
};

// Inicializar preview baseado no modelValue
const initializePreview = () => {
    if (props.modelValue instanceof File) {
        // Se é um arquivo, gerar preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(props.modelValue);
    } else if (props.modelValue && typeof props.modelValue === 'string') {
        // Se é uma string (URL), usar diretamente
        imagePreview.value = generateUrlPreview(props.modelValue);
    } else if (props.preview) {
        // Se tem preview externo, usar
        imagePreview.value = generateUrlPreview(props.preview);
    } else {
        // Nenhuma imagem
        imagePreview.value = null;
    }
};

// Inicializar na montagem
initializePreview();

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validar tamanho do arquivo
        if (file.size > props.maxSize * 1024) {
            alert(`Arquivo muito grande. Tamanho máximo: ${props.maxSize}KB`);
            return;
        }

        // Validar tipo de arquivo
        if (!file.type.startsWith('image/')) {
            alert('Por favor, selecione apenas arquivos de imagem.');
            return;
        }

        emit('update:modelValue', file);
        
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
            emit('update:preview', e.target.result);
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    // Quando remover, enviar null explicitamente
    emit('update:modelValue', null);
    imagePreview.value = null;
    emit('update:preview', null);
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const triggerFileInput = () => {
    if (fileInput.value) {
        fileInput.value.click();
    }
};

// Observar mudanças no modelValue
watch(() => props.modelValue, (newValue) => {
    if (newValue instanceof File) {
        // Novo arquivo selecionado
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(newValue);
    } else if (newValue && typeof newValue === 'string') {
        // URL de imagem existente
        imagePreview.value = generateUrlPreview(newValue);
    } else if (newValue === null || newValue === undefined) {
        // Se modelValue é null mas temos preview, manter o preview
        if (props.preview) {
            imagePreview.value = generateUrlPreview(props.preview);
        } else {
            imagePreview.value = null;
        }
    } else {
        // Nenhuma imagem
        imagePreview.value = null;
    }
}, { immediate: true });

// Observar mudanças no preview externo
watch(() => props.preview, (newValue) => {
    // Se não temos modelValue ou modelValue é null, usar o preview
    if (newValue && (!props.modelValue || props.modelValue === null)) {
        imagePreview.value = generateUrlPreview(newValue);
    }
}, { immediate: true });
</script>

<template>
    <div class="space-y-4">
        <input 
            ref="fileInput" 
            type="file" 
            :accept="accept" 
            @change="handleFileChange"
            class="hidden" 
        />
        
        <div v-if="!hasImage" class="border-2 border-dashed border-muted-foreground/25 rounded-lg p-4 sm:p-6">
            <div class="flex flex-col items-center gap-2 text-center">
                <Upload class="h-6 w-6 sm:h-8 sm:w-8 text-muted-foreground" />
                <div class="text-xs sm:text-sm text-muted-foreground">
                    {{ placeholder }}
                </div>
                <Button 
                    type="button" 
                    variant="outline" 
                    size="sm" 
                    @click="triggerFileInput"
                    class="mt-2"
                >
                    Selecionar {{ label }}
                </Button>
            </div>
        </div>
        
        <div v-else class="space-y-3">
            <div class="relative inline-block">
                <img 
                    :src="imagePreview" 
                    :alt="`Preview da ${label.toLowerCase()}`"
                    class="w-full h-32 sm:h-40 object-cover rounded-lg border" 
                />
                <Button 
                    type="button" 
                    variant="destructive" 
                    size="sm"
                    class="absolute -top-2 -right-2 h-6 w-6 p-0" 
                    @click="removeImage"
                >
                    <X class="h-3 w-3" />
                </Button>
            </div>
            
            <Button 
                type="button" 
                variant="outline" 
                size="sm" 
                @click="triggerFileInput"
            >
                <Upload class="h-4 w-4 mr-2" />
                Trocar {{ label }}
            </Button>
        </div>
    </div>
</template>