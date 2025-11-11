<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { Smartphone, QrCode, CheckCircle, Shield } from 'lucide-vue-next'

const props = defineProps({
  checkout: Object,
  sale: Object,
})

// Timer de expiração - começar em 15:00 e contar regressivamente
const timeRemaining = ref('15:00')
const totalSeconds = ref(15 * 60) // 15 minutos em segundos
let timerInterval = null

// Estado de cópia
const copied = ref(false)

// Calcular tempo restante
const calculateTimeRemaining = () => {
  // Calcular primeiro, depois decrementar
  const minutes = Math.floor(totalSeconds.value / 60)
  const seconds = totalSeconds.value % 60
  timeRemaining.value = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
  
  // Decrementar
  totalSeconds.value--
  
  // Se chegou a zero ou menos, resetar para 15:00
  if (totalSeconds.value < 0) {
    totalSeconds.value = 15 * 60
    timeRemaining.value = '15:00'
  }
}

// Copiar código PIX
const copyPixCode = async () => {
  if (!props.sale?.pix_qr_code) return

  try {
    await navigator.clipboard.writeText(props.sale.pix_qr_code)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (error) {
    console.error('Erro ao copiar código:', error)
  }
}

// Truncar código PIX para mostrar apenas uma parte
const truncatedPixCode = computed(() => {
  if (!props.sale?.pix_qr_code) return 'Carregando código...'
  const code = props.sale.pix_qr_code
  // Mostrar primeiros 40 caracteres + "..."
  if (code.length > 40) {
    return code.substring(0, 40) + '...'
  }
  return code
})

// QR Code image URL ou gerar do código
const qrCodeImageUrl = computed(() => {
  if (props.sale?.pix_qr_code_image) {
    // Se for base64
    if (props.sale.pix_qr_code_image.startsWith('data:image')) {
      return props.sale.pix_qr_code_image
    }
    // Se for URL
    return props.sale.pix_qr_code_image
  }
  // Gerar QR Code do código PIX usando API externa
  if (props.sale?.pix_qr_code) {
    return `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(props.sale.pix_qr_code)}`
  }
  return null
})

onMounted(() => {
  // Inicializar timer em 15:00
  totalSeconds.value = 15 * 60
  calculateTimeRemaining()
  
  // Atualizar timer a cada segundo
  timerInterval = setInterval(() => {
    calculateTimeRemaining()
  }, 1000)
})

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
})
</script>

<template>
  <Head title="Aguardar Pagamento" />
  
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <!-- Card Principal - Largura melhorada -->
    <div class="w-full max-w-5xl bg-white rounded-xl shadow-lg p-6 md:p-8">
      <!-- Conteúdo Principal - Duas Colunas -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-10">
        <!-- Coluna Esquerda - QR Code e PIX Copia e Cola -->
        <div class="flex flex-col space-y-6">
          <!-- Título e Timer (acima do QR Code) -->
          <div class="text-center">
            <h1 class="text-xl md:text-2xl font-extrabold text-gray-950 mb-2 leading-tight">
              Falta pouco! Para finalizar a compra,<br>
              escaneie o QR Code abaixo.
            </h1>
            <p class="text-sm text-gray-600">
              O código expira em: <span class="font-semibold text-red-600">{{ timeRemaining }}</span>
            </p>
          </div>

          <!-- QR Code -->
          <div class="flex flex-col items-center">
            <div class="bg-white p-4 rounded-lg border border-gray-300">
              <img 
                v-if="qrCodeImageUrl" 
                :src="qrCodeImageUrl" 
                alt="QR Code PIX"
                class="w-full max-w-[250px] h-auto"
              />
              <div v-else class="w-[250px] h-[250px] bg-gray-100 flex items-center justify-center rounded">
                <QrCode class="w-24 h-24 text-gray-400" />
              </div>
            </div>
          </div>

          <!-- PIX Copia e Cola (embaixo do QR Code) -->
          <div class="space-y-3">
            <h3 class="text-sm text-gray-900 leading-relaxed text-center">
              Se preferir, pague com a opção <span class="font-bold">PIX Copia e Cola</span>:
            </h3>
            
            <!-- Campo com código truncado - ajustado para não ter espaço extra -->
            <div class="flex justify-center">
              <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 inline-block max-w-full">
                <p class="text-xs text-gray-700 font-mono whitespace-nowrap overflow-hidden">
                  {{ truncatedPixCode }}
                </p>
              </div>
            </div>
            
            <!-- Botão Copiar -->
            <button 
              @click="copyPixCode"
              :class="[
                'w-full py-2.5 px-4 rounded-lg font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2',
                copied 
                  ? 'bg-green-600 text-white shadow-md' 
                  : 'bg-green-600 hover:bg-green-700 text-white shadow-sm hover:shadow-md'
              ]"
            >
              <CheckCircle v-if="copied" class="w-4 h-4" />
              {{ copied ? 'Código Copiado!' : 'COPIAR CÓDIGO' }}
            </button>
          </div>
        </div>

        <!-- Coluna Direita - Instruções (alinhadas verticalmente com QR Code) -->
        <div class="flex flex-col justify-center space-y-6">
          <h2 class="text-base md:text-lg font-semibold text-gray-900">Instruções para pagamento</h2>
          
          <div class="space-y-5">
            <!-- Instrução 1 -->
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                <Smartphone class="w-5 h-5 text-green-600" />
              </div>
              <div class="space-y-1 flex-1">
                <p class="text-sm md:text-base text-gray-900 font-semibold leading-tight">
                  Abra o app do seu banco e entre no ambiente Pix
                </p>
                <p class="text-sm text-gray-600 leading-relaxed">
                  Use o app do seu banco e vá até o Pix
                </p>
              </div>
            </div>

            <!-- Instrução 2 -->
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                <QrCode class="w-5 h-5 text-green-600" />
              </div>
              <div class="space-y-1 flex-1">
                <p class="text-sm md:text-base text-gray-900 font-semibold leading-tight">
                  Escolha Pagar com QR Code
                </p>
                <p class="text-sm text-gray-600 leading-relaxed">
                  Aponte a câmera para o código QR
                </p>
              </div>
            </div>

            <!-- Instrução 3 -->
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                <CheckCircle class="w-5 h-5 text-green-600" />
              </div>
              <div class="space-y-1 flex-1">
                <p class="text-sm md:text-base text-gray-900 font-semibold leading-tight">
                  Confirme as informações e finalize
                </p>
                <p class="text-sm text-gray-600 leading-relaxed">
                  Verifique os dados e conclua sua compra
                </p>
              </div>
            </div>
          </div>

          <!-- Logo PIX e Ambiente Seguro -->
          <div class="pt-4 border-t border-gray-200">
            <div class="flex items-center justify-center md:justify-start gap-2 md:gap-3 flex-wrap">
              <!-- Logo PIX aumentado -->
              <img 
                src="https://upload.wikimedia.org/wikipedia/commons/d/de/Logo_-_pix_powered_by_Banco_Central_%28Brazil%2C_2020%29.png" 
                alt="PIX Logo"
                class="w-[72px] md:w-20 h-auto flex-shrink-0"
              />
              <!-- Linha sutil -->
              <div class="h-6 md:h-8 w-px bg-gray-300"></div>
              <!-- Ambiente seguro -->
              <div class="flex items-center gap-1.5 md:gap-2">
                <Shield class="w-4 h-4 md:w-5 md:h-5 text-green-600 flex-shrink-0" />
                <span class="text-xs md:text-sm text-gray-600">Ambiente seguro</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Estilos adicionais para garantir fidelidade à imagem */
</style>
