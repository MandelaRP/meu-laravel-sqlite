<!-- eslint-disable vue/block-lang -->
<script setup>
import { CreditCardIcon, TrendingUpIcon, XIcon } from 'lucide-vue-next'

const props = defineProps({
    bannerUrl: {
        type: String,
        default: null
    }
});

defineEmits(['close'])
</script>

<template>
  <!-- Banner com imagem customizada -->
  <div v-if="bannerUrl" class="relative overflow-hidden rounded-xl shadow-lg w-[95%] sm:w-[90%] mx-auto banner-image-container">
    <!-- Botão de fechar -->
    <button 
      @click="$emit('close')"
      class="absolute top-2 right-2 z-20 p-1.5 sm:p-1 rounded-full bg-black/20 hover:bg-black/30 transition-colors backdrop-blur-sm"
    >
      <XIcon class="w-4 h-4 text-white" />
    </button>
    
    <!-- Imagem do banner -->
    <img 
      :src="bannerUrl" 
      alt="Banner Promocional"
      class="w-full h-full object-cover rounded-xl"
      style="object-fit: cover; width: 100%; height: 100%;"
    />
  </div>

  <!-- Banner padrão (fallback) -->
  <div v-else class="relative overflow-hidden bg-gradient-to-r from-emerald-500 via-emerald-600 to-blue-600 rounded-xl shadow-lg w-[95%] sm:w-[90%] mx-auto">
    <!-- Botão de fechar -->
    <button 
      @click="$emit('close')"
      class="absolute top-2 right-2 z-20 p-1.5 sm:p-1 rounded-full bg-white/20 hover:bg-white/30 transition-colors"
    >
      <XIcon class="w-4 h-4 text-white" />
    </button>

    <!-- Elementos decorativos de fundo -->
    <div class="absolute inset-0 overflow-hidden">
      <!-- Formas orgânicas 3D -->
      <div class="absolute top-2 right-4 w-16 h-16 sm:w-24 sm:h-24 bg-white/20 rounded-full blur-xl transform rotate-12"></div>
      <div class="absolute top-8 right-16 w-12 h-12 sm:w-16 sm:h-16 bg-white/15 rounded-full blur-lg transform -rotate-45"></div>
      <div class="absolute bottom-4 right-8 w-20 h-20 sm:w-32 sm:h-32 bg-white/10 rounded-full blur-2xl transform rotate-45"></div>
      
      <!-- Elementos geométricos adicionais -->
      <div class="absolute top-1/2 right-12 w-8 h-8 sm:w-12 sm:h-12 bg-white/25 rounded-full transform -translate-y-1/2 rotate-12"></div>
      <div class="absolute top-1/3 right-24 w-6 h-6 sm:w-8 sm:h-8 bg-white/20 rounded-full transform rotate-45"></div>
      
      <!-- Gradiente overlay para profundidade -->
      <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/50 to-transparent"></div>
    </div>

    <!-- Conteúdo principal - Desktop -->
    <div class="relative z-10 hidden lg:flex items-center justify-between p-6">
      <!-- Lado esquerdo - Texto principal -->
      <div class="flex-1 max-w-xl">
        <h2 class="text-2xl lg:text-3xl font-bold text-white mb-2 leading-tight">
          Aumente seus lucros
          <span class="text-white/90">sem alterar nada</span>
          <span class="text-yellow-300">na sua operação!</span>
        </h2>
        
        <p class="text-base lg:text-lg text-white/90 mb-1 font-medium">
          Use seu link de indicação e ganhe
          <span class="text-yellow-300 font-bold">2% de todas as vendas</span>
          feitas por empresas que você trouxer
        </p>
      </div>

      <!-- Lado direito - CTA e elementos visuais -->
      <div class="flex-shrink-0 ml-6 text-right">
        <!-- Logo/Brand -->
        <div class="flex items-center justify-end mb-4">
          <div class="w-6 h-6 bg-white rounded-lg flex items-center justify-center mr-2">
            <CreditCardIcon class="w-4 h-4 text-emerald-600" />
          </div>
          <div class="text-white">
            <div class="text-lg font-bold">LuckyPay</div>
            <div class="text-xs text-white/70">PAYMENT GATEWAY</div>
          </div>
        </div>

        <!-- Botão CTA -->
        <button class="group bg-white hover:bg-gray-50 text-emerald-600 font-bold py-2 px-6 rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 flex items-center space-x-2">
          <span class="text-base">INDIQUE E GANHE!</span>
          <TrendingUpIcon class="w-4 h-4 group-hover:rotate-12 transition-transform duration-300" />
        </button>
      </div>
    </div>

    <!-- Conteúdo principal - Mobile/Tablet -->
    <div class="relative z-10 lg:hidden p-4 sm:p-6">
      <!-- Logo mobile -->
      <div class="flex items-center justify-center mb-4">
        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-white rounded-lg flex items-center justify-center mr-3">
          <CreditCardIcon class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600" />
        </div>
        <div class="text-white">
          <div class="text-lg sm:text-xl font-bold">LuckyPay</div>
          <div class="text-xs text-white/70">PAYMENT GATEWAY</div>
        </div>
      </div>

      <!-- Texto principal mobile -->
      <div class="text-center mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-white mb-3 leading-tight">
          Aumente seus lucros
          <span class="block text-yellow-300">sem alterar nada!</span>
        </h2>
        
        <p class="text-sm sm:text-base text-white/90 mb-4 leading-relaxed">
          Use seu link de indicação e ganhe
          <span class="text-yellow-300 font-bold">2% de todas as vendas</span>
          feitas por empresas que você trouxer
        </p>
      </div>

      <!-- Botão CTA mobile -->
      <div class="flex justify-center">
        <button class="group bg-white hover:bg-gray-50 text-emerald-600 font-bold py-3 px-6 rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 flex items-center space-x-2 text-sm sm:text-base">
          <span>INDIQUE E GANHE!</span>
          <TrendingUpIcon class="w-4 h-4 group-hover:rotate-12 transition-transform duration-300" />
        </button>
      </div>
    </div>

    <!-- Elementos decorativos flutuantes -->
    <div class="absolute top-4 left-1/4 w-2 h-2 bg-yellow-300 rounded-full animate-pulse"></div>
    <div class="absolute bottom-4 left-1/3 w-1.5 h-1.5 bg-white/60 rounded-full animate-bounce" style="animation-delay: 0.5s"></div>
    <div class="absolute top-1/2 left-1/2 w-1 h-1 bg-yellow-300/80 rounded-full animate-ping" style="animation-delay: 1s"></div>
  </div>
</template>

<style scoped>
/* Dimensões responsivas do banner de imagem */
.banner-image-container {
  height: 250px; /* Mobile: ~341x250px */
}

@media (min-width: 640px) {
  .banner-image-container {
    height: 220px; /* Tablet: ~680x220px */
  }
}

@media (min-width: 1024px) {
  .banner-image-container {
    height: 168px; /* Desktop: 1123x168px */
  }
}

@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-10px) rotate(5deg); }
}

.animate-float {
  animation: float 3s ease-in-out infinite;
}

/* Animação personalizada para os elementos de fundo */
@keyframes blob {
  0%, 100% { transform: translate(0px, 0px) scale(1) rotate(0deg); }
  33% { transform: translate(10px, -10px) scale(1.1) rotate(120deg); }
  66% { transform: translate(-5px, 5px) scale(0.9) rotate(240deg); }
}

.animate-blob {
  animation: blob 7s infinite;
}

/* Responsividade aprimorada */
@media (max-width: 1024px) {
  .lg\:hidden {
    display: block;
  }
  
  .hidden {
    display: none;
  }
}

/* Melhorias para mobile */
@media (max-width: 640px) {
  .banner-mobile {
    margin: 0.5rem;
    border-radius: 0.75rem;
  }
  
  .text-mobile {
    font-size: 1.125rem;
    line-height: 1.5;
  }
  
  .button-mobile {
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
  }
}

/* Animações suaves para transições */
.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>