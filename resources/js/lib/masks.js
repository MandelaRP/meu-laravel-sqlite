export const maskToPhone = (value) => {
    if (!value) return ''
    
    value = value.replace(/\D/g, '')
    value = value.replace(/(\d{2})(\d)/, '($1) $2')
    value = value.replace(/(\d{5})(\d)/, '$1-$2')
    value = value.replace(/(-\d{4})\d+?$/, '$1')
    
    return value
}

export const maskToCPF = (value) => {
    if (!value) return ''
    
    value = value.replace(/\D/g, '')
    value = value.replace(/(\d{3})(\d)/, '$1.$2')
    value = value.replace(/(\d{3})(\d)/, '$1.$2')
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2')
    value = value.replace(/(-\d{2})\d+?$/, '$1')
    
    return value
}

export const maskToCNPJ = (value) => {
    if (!value) return ''
    
    value = value.replace(/\D/g, '')
    value = value.replace(/(\d{2})(\d)/, '$1.$2')
    value = value.replace(/(\d{3})(\d)/, '$1.$2')
    value = value.replace(/(\d{3})(\d)/, '$1/$2')
    value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2')
    value = value.replace(/(-\d{2})\d+?$/, '$1')
    
    return value
}

export const maskToCurrency = (value) => {
    if (!value) return ''
    
    value = value.replace(/\D/g, '')
    value = (Number(value) / 100).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    })
    
    return value
} 