<!-- eslint-disable vue/block-lang -->
<script setup>
import { CreditCardIcon, ZapIcon, WalletIcon, ShieldCheckIcon, TrendingUpIcon } from 'lucide-vue-next'
</script>

<template>
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
</template>

<style scoped>
.shadow-3xl {
  box-shadow: 0 35px 60px -12px rgba(0, 0, 0, 0.25);
}
</style>