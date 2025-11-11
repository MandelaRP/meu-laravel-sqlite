<script setup>
import { CreditCardIcon, ZapIcon, WalletIcon, ShieldCheckIcon, TrendingUpIcon } from 'lucide-vue-next'
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 flex items-center justify-center p-8">
    <div class="text-center space-y-12">
      <!-- Logo Principal -->
      <div class="relative">
        <!-- Versão Principal da Logo -->
        <div class="bg-white rounded-2xl shadow-2xl p-12 border border-gray-100 hover:shadow-3xl transition-all duration-300">
          <div class="flex items-center justify-center space-x-4">
            <!-- Ícone da Logo -->
            <div class="relative">
              <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                <CreditCardIcon class="w-8 h-8 text-white" />
              </div>
              <!-- Elemento decorativo -->
              <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center">
                <ZapIcon class="w-3 h-3 text-white" />
              </div>
            </div>
            
            <!-- Nome da Marca -->
            <div class="text-left">
              <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
                Lucky<span class="text-slate-700">Pay</span>
              </h1>
              <p class="text-sm text-slate-500 font-medium tracking-wide">PAYMENT GATEWAY</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Variações da Logo -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl">
        <!-- Logo Horizontal Compacta -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
          <h3 class="text-xs text-slate-400 mb-4 uppercase tracking-wide">Horizontal</h3>
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-blue-600 rounded-lg flex items-center justify-center">
              <CreditCardIcon class="w-5 h-5 text-white" />
            </div>
            <div class="text-2xl font-bold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
              LuckyPay
            </div>
          </div>
        </div>

        <!-- Logo Vertical -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
          <h3 class="text-xs text-slate-400 mb-4 uppercase tracking-wide">Vertical</h3>
          <div class="flex flex-col items-center space-y-2">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-blue-600 rounded-xl flex items-center justify-center">
              <CreditCardIcon class="w-6 h-6 text-white" />
            </div>
            <div class="text-xl font-bold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
              LuckyPay
            </div>
            <div class="text-xs text-slate-500 font-medium">GATEWAY</div>
          </div>
        </div>

        <!-- Logo Minimalista -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
          <h3 class="text-xs text-slate-400 mb-4 uppercase tracking-wide">Minimal</h3>
          <div class="flex items-center justify-center">
            <div class="text-2xl font-bold text-slate-700">
              Lucky<span class="text-emerald-600">Pay</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Logo em Fundo Escuro -->
      <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl shadow-2xl p-12">
        <h3 class="text-xs text-slate-400 mb-6 uppercase tracking-wide text-center">Dark Version</h3>
        <div class="flex items-center justify-center space-x-4">
          <div class="relative">
            <div class="w-16 h-16 bg-gradient-to-br from-emerald-400 to-blue-500 rounded-xl flex items-center justify-center shadow-lg">
              <CreditCardIcon class="w-8 h-8 text-white" />
            </div>
            <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center">
              <ZapIcon class="w-3 h-3 text-white" />
            </div>
          </div>
          <div class="text-left">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-400 to-blue-400 bg-clip-text text-transparent">
              Lucky<span class="text-white">Pay</span>
            </h1>
            <p class="text-sm text-slate-400 font-medium tracking-wide">PAYMENT GATEWAY</p>
          </div>
        </div>
      </div>

      <!-- Ícones Alternativos -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-2xl">
        <div class="bg-white rounded-lg shadow-md p-4 text-center">
          <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-blue-600 rounded-lg flex items-center justify-center mx-auto mb-2">
            <CreditCardIcon class="w-6 h-6 text-white" />
          </div>
          <p class="text-xs text-slate-500">Credit Card</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-4 text-center">
          <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-blue-600 rounded-lg flex items-center justify-center mx-auto mb-2">
            <WalletIcon class="w-6 h-6 text-white" />
          </div>
          <p class="text-xs text-slate-500">Wallet</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-4 text-center">
          <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-blue-600 rounded-lg flex items-center justify-center mx-auto mb-2">
            <ShieldCheckIcon class="w-6 h-6 text-white" />
          </div>
          <p class="text-xs text-slate-500">Security</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-4 text-center">
          <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-blue-600 rounded-lg flex items-center justify-center mx-auto mb-2">
            <TrendingUpIcon class="w-6 h-6 text-white" />
          </div>
          <p class="text-xs text-slate-500">Growth</p>
        </div>
      </div>

      <!-- Informações da Marca -->
      <div class="bg-white rounded-xl shadow-lg p-8 max-w-2xl border border-gray-100">
        <h3 class="text-lg font-semibold text-slate-700 mb-4">Identidade da Marca</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
          <div>
            <h4 class="font-medium text-slate-600 mb-2">Cores Primárias</h4>
            <div class="flex space-x-2">
              <div class="w-8 h-8 bg-emerald-500 rounded border-2 border-white shadow-sm"></div>
              <div class="w-8 h-8 bg-blue-600 rounded border-2 border-white shadow-sm"></div>
              <div class="w-8 h-8 bg-slate-700 rounded border-2 border-white shadow-sm"></div>
            </div>
          </div>
          <div>
            <h4 class="font-medium text-slate-600 mb-2">Cores Secundárias</h4>
            <div class="flex space-x-2">
              <div class="w-8 h-8 bg-yellow-400 rounded border-2 border-white shadow-sm"></div>
              <div class="w-8 h-8 bg-orange-500 rounded border-2 border-white shadow-sm"></div>
              <div class="w-8 h-8 bg-slate-400 rounded border-2 border-white shadow-sm"></div>
            </div>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <p class="text-slate-600 text-center">
            <strong>LuckyPay</strong> - Sua gateway de pagamentos confiável e moderna
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.shadow-3xl {
  box-shadow: 0 35px 60px -12px rgba(0, 0, 0, 0.25);
}
</style>