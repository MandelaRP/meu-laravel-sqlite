import { useForm } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'

export function useCheckoutActions(checkout = null) {
  const submitForm = (form, isSubmitting) => {
    isSubmitting.value = true

    if (checkout) {
      // Edição - Inertia.js já lida com arquivos automaticamente
      // Se o banner foi removido (null), enviar 'remove' explicitamente
      const formData = { ...form.data() }
      if (formData.banner === null && checkout.banner) {
        formData.banner = 'remove'
      }
      
      // Usar transform para garantir que o banner e form_fields_config sejam tratados corretamente
      form.transform((data) => {
        const transformed = { ...data }
        // Se o banner foi removido, enviar 'remove'
        if (transformed.banner === null && checkout.banner) {
          transformed.banner = 'remove'
        }
        // IMPORTANTE: Garantir que form_fields_config seja sempre enviado
        // Preservar EXATAMENTE os valores do formulário, sem modificações
        if (transformed.form_fields_config) {
          // Criar cópia profunda para garantir que não há referências
          // Isso garante que os valores sejam preservados exatamente como estão
          transformed.form_fields_config = JSON.parse(JSON.stringify(transformed.form_fields_config))
        }
        return transformed
      }).post(route('checkout.update', checkout.id), {
        preserveScroll: true,
        forceFormData: true, // Forçar FormData para enviar arquivos
        onSuccess: () => {
          toast.success('Checkout atualizado com sucesso!')
        },
        onError: () => {
          toast.error('Erro ao atualizar checkout')
        },
        onFinish: () => {
          isSubmitting.value = false
        },
      })
    } else {
      // Criação
      form.post(route('checkout.store'), {
        onSuccess: () => {
          toast.success('Checkout criado com sucesso!')
        },
        onError: () => {
          toast.error('Erro ao criar checkout')
        },
        onFinish: () => {
          isSubmitting.value = false
        },
      })
    }
  }

  const deleteCheckout = (checkoutId) => {
    useForm().delete(route('checkout.destroy', checkoutId), {
      onSuccess: () => {
        toast.success('Checkout excluído com sucesso!')
      },
      onError: () => {
        toast.error('Erro ao excluir checkout')
      },
    })
  }

  const copyCheckoutUrl = (checkoutId) => {
    const url = `${typeof window !== 'undefined' ? window.location.origin : ''}/checkout/${checkoutId}`
    if (typeof navigator !== 'undefined' && navigator.clipboard) {
      navigator.clipboard.writeText(url)
      toast.success('URL copiada para a área de transferência!')
    }
  }

  const openCheckout = (checkoutId) => {
    const url = `/checkout/${checkoutId}`
    if (typeof window !== 'undefined') {
      window.open(url, '_blank')
    }
  }

  return {
    submitForm,
    deleteCheckout,
    copyCheckoutUrl,
    openCheckout
  }
} 