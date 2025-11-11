// Configuração das cores para as formas de pagamento
export const paymentMethodColors = {
    pix: {
        border: 'border-green-500 dark:border-green-400',
        bg: 'bg-green-50/50 dark:bg-green-900/30',
        shadow: 'shadow-lg shadow-green-500/20 dark:shadow-green-400/30',
        iconBg: 'bg-gradient-to-br from-green-500 to-green-600 dark:from-green-400 dark:to-green-500 shadow-lg dark:shadow-green-400/50',
        checkMark: 'bg-green-500 dark:bg-green-400',
        titleSelected: 'text-green-700 dark:text-green-500',
        titleDefault: 'text-gray-300 dark:text-white',
        descriptionSelected: 'text-green-600 dark:text-green-300',
        descriptionDefault: 'text-gray-400 dark:text-gray-300',
        radioSelected: 'border-green-500 bg-green-500 dark:border-green-400 dark:bg-green-400',
        radioDefault: 'border-gray-300 dark:border-gray-500',
        badge: 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200',
        hoverBorder: 'hover:border-green-300 dark:hover:border-green-400',
        hoverBg: 'hover:bg-green-50/30 dark:hover:bg-green-900/20',
        defaultBorder: 'border-gray-200 dark:border-gray-600',
        defaultBg: 'dark:bg-gray-800'
    },
    credit_card: {
        border: 'border-blue-500 dark:border-blue-400',
        bg: 'bg-blue-50/50 dark:bg-blue-900/30',
        shadow: 'shadow-lg shadow-blue-500/20 dark:shadow-blue-400/30',
        iconBg: 'bg-gradient-to-br from-blue-500 to-blue-600 dark:from-blue-400 dark:to-blue-500 shadow-lg dark:shadow-blue-400/50',
        checkMark: 'bg-blue-500 dark:bg-blue-400',
        titleSelected: 'text-blue-700 dark:text-gray-200',
        titleDefault: 'text-gray-300 dark:text-white',
        descriptionSelected: 'text-blue-600 dark:text-blue-300',
        descriptionDefault: 'text-gray-400 dark:text-gray-300',
        radioSelected: 'border-blue-500 bg-blue-500 dark:border-blue-400 dark:bg-blue-400',
        radioDefault: 'border-gray-300 dark:border-gray-500',
        badge: 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200',
        hoverBorder: 'hover:border-blue-300 dark:hover:border-blue-400',
        hoverBg: 'hover:bg-blue-50/30 dark:hover:bg-blue-900/20',
        defaultBorder: 'border-gray-200 dark:border-gray-600',
        defaultBg: 'dark:bg-gray-800'
    },
    boleto: {
        border: 'border-orange-500 dark:border-orange-400',
        bg: 'bg-orange-50/50 dark:bg-orange-900/30',
        shadow: 'shadow-lg shadow-orange-500/20 dark:shadow-orange-400/30',
        iconBg: 'bg-gradient-to-br from-orange-500 to-orange-600 dark:from-orange-400 dark:to-orange-500 shadow-lg dark:shadow-orange-400/50',
        checkMark: 'bg-orange-500 dark:bg-orange-400',
        titleSelected: 'text-orange-700 dark:text-orange-300',
        titleDefault: 'text-gray-300 dark:text-white',
        descriptionSelected: 'text-orange-600 dark:text-orange-300',
        descriptionDefault: 'text-gray-400 dark:text-gray-300',
        radioSelected: 'border-orange-500 bg-orange-500 dark:border-orange-400 dark:bg-orange-400',
        radioDefault: 'border-gray-300 dark:border-gray-500',
        badge: 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-200',
        hoverBorder: 'hover:border-orange-300 dark:hover:border-orange-400',
        hoverBg: 'hover:bg-orange-50/30 dark:hover:bg-orange-900/20',
        defaultBorder: 'border-gray-200 dark:border-gray-600',
        defaultBg: 'dark:bg-gray-800'
    }
}

// Configuração dos métodos de pagamento
export const paymentMethods = [
    {
        method: 'pix',
        title: 'PIX',
        description: 'Pagamento instantâneo e seguro',
        isImageIcon: true,
        iconSrc: '/images/icons/icon-pix.png'
    },
    {
        method: 'credit_card',
        title: 'Cartão de Crédito',
        description: 'Parcele em até 12x sem juros',
        isImageIcon: false,
        icon: null // Será definido dinamicamente no componente
    },
    {
        method: 'boleto',
        title: 'Boleto Bancário',
        description: 'Pague em até 3 dias úteis',
        isImageIcon: false,
        icon: null // Será definido dinamicamente no componente
    }
] 