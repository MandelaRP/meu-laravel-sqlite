<script setup>
import { ref, computed, onMounted } from 'vue';
import { Label } from '@/components/ui/label';

const props = defineProps({
    modelValue: { 
        type: [Number, String],
        default: null
    },
    placeholder: {
        type: String,
        default: '0,00',
    },
    class: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: null,
    },
    label: {
        type: String,
        default: '',
    },
    hideLabel: {
        type: Boolean,
        default: false,
    },
    id: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const displayValue = ref('');
const isFocused = ref(false);

// Converter número para string formatada (ex: 19.9 → "19,90")
const formatDisplay = (num) => {
    if (num === null || num === undefined || num === '' || num === 0) {
        return '';
    }
    const numVal = typeof num === 'string' ? parseFloat(num) : num;
    if (isNaN(numVal) || numVal === 0) {
        return '';
    }
    return numVal.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

// Converter string formatada para número (ex: "19,90" → 19.9)
const parseValue = (str) => {
    if (!str || str.trim() === '') {
        return 0;
    }
    
    // Remove tudo exceto números, vírgula e ponto
    let cleaned = str.replace(/[^\d,\.]/g, '').trim();
    
    if (!cleaned) {
        return 0;
    }
    
    // Se tem vírgula, é formato brasileiro
    if (cleaned.includes(',')) {
        // Remove pontos (milhares) e converte vírgula para ponto
        cleaned = cleaned.replace(/\./g, '').replace(',', '.');
    }
    
    const num = parseFloat(cleaned);
    return isNaN(num) ? 0 : num;
};


// Debounce para emit - evitar recálculos excessivos durante digitação rápida
let emitTimeout = null;

const handleInput = (e) => {
    // Obter valor diretamente do input
    const raw = e.target.value;
    
    // Limpeza rápida
    let cleaned = raw.replace(/[^\d,\.]/g, '');
    
    // Processar vírgula
    const commaPos = cleaned.indexOf(',');
    if (commaPos >= 0) {
        const before = cleaned.substring(0, commaPos + 1);
        const after = cleaned.substring(commaPos + 1).replace(/,/g, '');
        cleaned = before + (after.length > 2 ? after.substring(0, 2) : after);
    } else {
        // Processar ponto
        const dotPos = cleaned.indexOf('.');
        if (dotPos >= 0) {
            const before = cleaned.substring(0, dotPos + 1);
            const after = cleaned.substring(dotPos + 1).replace(/\./g, '');
            cleaned = before + (after.length > 2 ? after.substring(0, 2) : after);
        }
    }
    
    // Atualizar display IMEDIATAMENTE - resposta visual instantânea
    displayValue.value = cleaned;
    
    // Limpar timeout anterior
    if (emitTimeout) {
        clearTimeout(emitTimeout);
    }
    
    // Emitir com pequeno delay para não bloquear durante digitação rápida
    // Mas manter resposta visual imediata
    emitTimeout = setTimeout(() => {
        const parsed = parseValue(cleaned);
        emit('update:modelValue', parsed);
        emitTimeout = null;
    }, 50); // 50ms de delay - imperceptível para usuário mas evita bloqueio
};

const handleBlur = () => {
    // Limpar qualquer timeout pendente
    if (emitTimeout) {
        clearTimeout(emitTimeout);
        emitTimeout = null;
    }
    
    // Formatar ao perder foco
    const parsed = parseValue(displayValue.value);
    if (parsed > 0) {
        // Garantir que valores como 349.00 sejam mantidos corretamente
        const preciseValue = parseFloat(parsed.toFixed(2));
        displayValue.value = formatDisplay(preciseValue);
        emit('update:modelValue', preciseValue);
    } else {
        displayValue.value = '';
        emit('update:modelValue', 0);
    }
};

const handleKeyDown = (e) => {
    // Permitir teclas de controle
    if (e.ctrlKey || e.metaKey || e.altKey) {
        return;
    }
    
    const allowedKeys = [
        'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
        'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
        'Home', 'End'
    ];
    
    if (allowedKeys.includes(e.key)) {
        return;
    }
    
    // Permitir apenas números, vírgula e ponto
    if (!/[\d,\.]/.test(e.key)) {
        e.preventDefault();
    }
};

const handleFocusEvent = () => {
    isFocused.value = true;
};

const handleBlurEvent = () => {
    isFocused.value = false;
    handleBlur();
};

// Inicializar valor apenas no mount - sem watch para evitar delay durante digitação
onMounted(() => {
    if (props.modelValue !== null && props.modelValue !== undefined && props.modelValue !== '' && props.modelValue !== 0) {
        displayValue.value = formatDisplay(props.modelValue);
    } else {
        displayValue.value = '';
    }
});

const inputClasses = computed(() => {
    let classes = props.class || '';
    if (props.error) {
        classes += ' border-destructive focus:border-destructive focus:ring-destructive';
    }
    return classes;
});
</script>

<template>
    <Label v-if="!hideLabel && label" :for="id || 'money'" class="text-sm font-medium">{{ label }}</Label>
    <input
        type="text"
        inputmode="decimal"
        :value="displayValue"
        @input="handleInput"
        @keydown="handleKeyDown"
        @focus="handleFocusEvent"
        @blur="handleBlurEvent"
        :placeholder="placeholder"
        :class="[inputClasses, 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm h-10 sm:h-9 text-sm']"
        :id="id || 'money'"
    />
</template>


