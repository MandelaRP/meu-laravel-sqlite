<template>
  <div class="space-y-4 md:space-y-6">
    <!-- Cores de Fundo e Texto -->
    <Card>
      <CardHeader class="px-3 md:px-6">
        <CardTitle class="flex items-center gap-2 text-sm md:text-base">
          <LayoutGrid class="h-4 w-4 md:h-5 md:w-5" />
          Cores de Fundo e Texto
        </CardTitle>
        <CardDescription class="text-xs md:text-sm">
          Personalize as cores de fundo e texto do seu checkout
        </CardDescription>
        <div v-if="showResetButtons" class="flex justify-end">
          <Button @click="resetBackgroundColors" variant="outline" size="sm" class="text-xs">
            Reset Cores
          </Button>
        </div>
      </CardHeader>
      <CardContent class="px-3 md:px-6">
        <div class="space-y-3 md:space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
            <div class="space-y-2">
              <Label for="background_color" class="text-xs md:text-sm">Cor de fundo principal</Label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="form.background_color" id="background_color"
                  class="w-8 h-8 md:w-10 md:h-10 rounded-md border border-input cursor-pointer" />
                <Input v-model="form.background_color" class="flex-1 text-xs md:text-sm" />
              </div>
            </div>

            <div class="space-y-2">
              <Label for="text_color" class="text-xs md:text-sm">Cor do texto</Label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="form.text_color" id="text_color"
                  class="w-8 h-8 md:w-10 md:h-10 rounded-md border border-input cursor-pointer" />
                <Input v-model="form.text_color" class="flex-1 text-xs md:text-sm" />
              </div>
            </div>
          </div>

          <!-- Preview do fundo e texto -->
          <div class="mt-3 md:mt-4 p-3 md:p-4 rounded-md border"
            :style="{ backgroundColor: form.background_color, color: form.text_color }">
            <div class="text-center">
              <p class="text-xs md:text-sm">Preview do fundo e texto</p>
              <p class="text-xs opacity-70">Esta será a aparência do seu checkout</p>
              <p class="text-xs md:text-sm mt-2">Texto de exemplo para demonstrar a cor do texto</p>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Cores dos Botões -->
    <Card>
      <CardHeader class="px-3 md:px-6">
        <CardTitle class="flex items-center gap-2 text-sm md:text-base">
          <LayoutGrid class="h-4 w-4 md:h-5 md:w-5" />
          Cores dos Botões
        </CardTitle>
        <CardDescription class="text-xs md:text-sm">
          Personalize as cores dos botões do seu checkout
        </CardDescription>
        <div v-if="showResetButtons" class="flex justify-end">
          <Button @click="resetButtonColors" variant="outline" size="sm" class="text-xs">
            Reset Cores
          </Button>
        </div>
      </CardHeader>
      <CardContent class="px-3 md:px-6">
        <div class="space-y-4 md:space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            <div class="space-y-3 md:space-y-4">
              <h4 class="font-medium text-xs md:text-sm">Botão Primário</h4>
              <div class="space-y-2 md:space-y-3">
                <div class="space-y-2">
                  <Label for="button_primary_color" class="text-xs md:text-sm">Cor de fundo</Label>
                  <div class="flex items-center gap-2">
                    <input type="color" v-model="form.button_primary_color" id="button_primary_color"
                      class="w-8 h-8 md:w-10 md:h-10 rounded-md border border-input cursor-pointer" />
                    <Input v-model="form.button_primary_color" class="flex-1 text-xs md:text-sm" />
                  </div>
                </div>
                <div class="space-y-2">
                  <Label for="button_hover_primary_color" class="text-xs md:text-sm">Cor de fundo (hover)</Label>
                  <div class="flex items-center gap-2">
                    <input type="color" v-model="form.button_hover_primary_color"
                      id="button_hover_primary_color"
                      class="w-8 h-8 md:w-10 md:h-10 rounded-md border border-input cursor-pointer" />
                    <Input v-model="form.button_hover_primary_color" class="flex-1 text-xs md:text-sm" />
                  </div>
                </div>
              </div>
            </div>

            <div class="space-y-3 md:space-y-4">
              <h4 class="font-medium text-xs md:text-sm">Botão Secundário</h4>
              <div class="space-y-2 md:space-y-3">
                <div class="space-y-2">
                  <Label for="button_secondary_color" class="text-xs md:text-sm">Cor de fundo</Label>
                  <div class="flex items-center gap-2">
                    <input type="color" v-model="form.button_secondary_color" id="button_secondary_color"
                      class="w-8 h-8 md:w-10 md:h-10 rounded-md border border-input cursor-pointer" />
                    <Input v-model="form.button_secondary_color" class="flex-1 text-xs md:text-sm" />
                  </div>
                </div>
                <div class="space-y-2">
                  <Label for="button_hover_secondary_color" class="text-xs md:text-sm">Cor de fundo (hover)</Label>
                  <div class="flex items-center gap-2">
                    <input type="color" v-model="form.button_hover_secondary_color"
                      id="button_hover_secondary_color"
                      class="w-8 h-8 md:w-10 md:h-10 rounded-md border border-input cursor-pointer" />
                    <Input v-model="form.button_hover_secondary_color" class="flex-1 text-xs md:text-sm" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Preview dos Botões -->
          <div class="space-y-3 md:space-y-4">
            <h4 class="font-medium text-xs md:text-sm">Preview dos Botões</h4>
            <div class="flex flex-wrap gap-2 md:gap-4">
              <button class="px-4 md:px-6 py-2 rounded-md text-white font-medium transition-colors text-xs md:text-sm"
                :style="{ backgroundColor: hoveredPrimary ? form.button_hover_primary_color : form.button_primary_color }"
                @mouseenter="hoveredPrimary = true" @mouseleave="hoveredPrimary = false">
                Botão Primário
              </button>
              <button class="px-4 md:px-6 py-2 rounded-md text-white font-medium transition-colors text-xs md:text-sm"
                :style="{ backgroundColor: hoveredSecondary ? form.button_hover_secondary_color : form.button_secondary_color }"
                @mouseenter="hoveredSecondary = true" @mouseleave="hoveredSecondary = false">
                Botão Secundário
              </button>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Cores dos Passos -->
    <Card>
      <CardHeader class="px-3 md:px-6">
        <CardTitle class="flex items-center gap-2 text-sm md:text-base">
          <LayoutGrid class="h-4 w-4 md:h-5 md:w-5" />
          Cores dos Passos
        </CardTitle>
        <CardDescription class="text-xs md:text-sm">
          Personalize as cores dos passos do checkout
        </CardDescription>
        <div v-if="showResetButtons" class="flex justify-end">
          <Button @click="resetStepsColors" variant="outline" size="sm" class="text-xs">
            Reset Cores
          </Button>
        </div>
      </CardHeader>
      <CardContent class="px-3 md:px-6">
        <div class="space-y-4 md:space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
            <div v-for="(step, key) in form.steps" :key="key" class="space-y-2 md:space-y-3">
              <h4 class="font-medium text-xs md:text-sm capitalize">{{ step.title }}</h4>
              <div class="space-y-2">
                <Label :for="`${key}_bg`" class="text-xs md:text-sm">Cor de fundo</Label>
                <div class="flex items-center gap-2">
                  <input type="color" v-model="step.bg_color" :id="`${key}_bg`"
                    class="w-6 h-6 md:w-8 md:h-8 rounded-md border border-input cursor-pointer" />
                  <Input v-model="step.bg_color" class="flex-1 text-xs" />
                </div>
              </div>
              <div class="space-y-2">
                <Label :for="`${key}_text`" class="text-xs md:text-sm">Cor do texto</Label>
                <div class="flex items-center gap-2">
                  <input type="color" v-model="step.text_color" :id="`${key}_text`"
                    class="w-6 h-6 md:w-8 md:h-8 rounded-md border border-input cursor-pointer" />
                  <Input v-model="step.text_color" class="flex-1 text-xs" />
                </div>
              </div>
              <div class="space-y-2">
                <Label :for="`${key}_border`" class="text-xs md:text-sm">Cor da borda</Label>
                <div class="flex items-center gap-2">
                  <input type="color" v-model="step.border_color" :id="`${key}_border`"
                    class="w-6 h-6 md:w-8 md:h-8 rounded-md border border-input cursor-pointer" />
                  <Input v-model="step.border_color" class="flex-1 text-xs" />
                </div>
              </div>
              <div class="space-y-2">
                <Label :for="`${key}_icon_check_bg`" class="text-xs md:text-sm">Background do ícone de check</Label>
                <div class="flex items-center gap-2">
                  <input type="color" v-model="step.icon_check_bg" :id="`${key}_icon_check_bg`"
                    class="w-6 h-6 md:w-8 md:h-8 rounded-md border border-input cursor-pointer" />
                  <Input v-model="step.icon_check_bg" class="flex-1 text-xs" />
                </div>
              </div>
              <div class="space-y-2">
                <Label :for="`${key}_icon_check_color`" class="text-xs md:text-sm">Cor do texto do ícone de check</Label>
                <div class="flex items-center gap-2">
                  <input type="color" v-model="step.icon_check_color" :id="`${key}_icon_check_color`"
                    class="w-6 h-6 md:w-8 md:h-8 rounded-md border border-input cursor-pointer" />
                  <Input v-model="step.icon_check_color" class="flex-1 text-xs" />
                </div>
              </div>
            </div>
          </div>

          <!-- Preview dos passos -->
          <div class="space-y-2 md:space-y-3">
            <h4 class="font-medium text-xs md:text-sm">Preview dos Passos</h4>
            <div class="flex flex-col md:flex-row gap-2">
              <div v-for="(step, key) in form.steps" :key="key"
                class="flex-1 p-2 md:p-3 rounded-md border text-center text-xs md:text-sm font-medium"
                :style="{ backgroundColor: step.bg_color, color: step.text_color, borderColor: step.border_color }">
                <div class="flex items-center justify-center gap-1 md:gap-2">
                  <p class="truncate">{{ step.title }}</p>
                  <CheckCircle class="h-3 w-3 md:h-4 md:w-4 rounded-full flex-shrink-0"
                    :style="{ backgroundColor: step.icon_check_bg, color: step.icon_check_color }" />
                </div>
                <p class="text-xs truncate">{{ step.description }}</p>
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { LayoutGrid, CheckCircle } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'

defineProps({
  form: Object,
  hoveredPrimary: Boolean,
  hoveredSecondary: Boolean,
  showResetButtons: {
    type: Boolean,
    default: false
  },
  resetBackgroundColors: Function,
  resetButtonColors: Function,
  resetStepsColors: Function,
})
</script> 