<template>
  <Card>
    <CardHeader class="px-3 md:px-6">
      <CardTitle class="flex items-center gap-2 text-sm md:text-base">
        <Clock class="h-4 w-4 md:h-5 md:w-5" />
        Contagem Regressiva
      </CardTitle>
      <CardDescription class="text-xs md:text-sm">
        Configure a contagem regressiva para criar urgência
      </CardDescription>
      <div v-if="showResetButtons" class="flex justify-end">
        <Button @click="resetCountdownColors" variant="outline" size="sm" class="text-xs">
          Reset Cores
        </Button>
      </div>
    </CardHeader>
    <CardContent class="px-3 md:px-6">
      <div class="space-y-4 md:space-y-6">
        <div class="flex items-center space-x-2">
          <Switch v-model="form.countdown_enabled" id="countdown_enabled" />
          <Label for="countdown_enabled" class="font-medium flex items-center gap-2 text-sm md:text-base">
            <Clock class="h-3 w-3 md:h-4 md:w-4" />
            Contagem Regressiva
          </Label>
        </div>

        <div v-show="form.countdown_enabled" class="space-y-3 md:space-y-4">
          <div class="space-y-2">
            <Label for="countdown_icon" class="text-xs md:text-sm">Emoji</Label>
            <Input v-model="form.countdown_icon" id="countdown_icon" placeholder="Ex: 🔥" class="text-xs md:text-sm" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
            <div class="space-y-2">
              <Label for="countdown_duration" class="text-xs md:text-sm">Duração</Label>
              <Select v-model="form.countdown_duration" id="countdown_duration">
                <SelectTrigger class="text-xs md:text-sm">
                  <SelectValue placeholder="Selecione uma duração" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="duration in countdownDurations" :key="duration.value"
                    :value="duration.value" class="text-xs md:text-sm">
                    {{ duration.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label for="countdown_message" class="text-xs md:text-sm">Mensagem</Label>
              <Input v-model="form.countdown_message" id="countdown_message"
                placeholder="Ex: Oferta por tempo limitado!" class="text-xs md:text-sm" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
            <div class="space-y-2">
              <Label for="countdown_bg_color" class="text-xs md:text-sm">Cor de fundo</Label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="form.countdown_bg_color" id="countdown_bg_color"
                  class="w-8 h-8 md:w-10 md:h-10 rounded-md border border-input cursor-pointer" />
                <Input v-model="form.countdown_bg_color" class="flex-1 text-xs md:text-sm" />
              </div>
            </div>

            <div class="space-y-2">
              <Label for="countdown_text_color" class="text-xs md:text-sm">Cor do texto</Label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="form.countdown_text_color" id="countdown_text_color"
                  class="w-8 h-8 md:w-10 md:h-10 rounded-md border border-input cursor-pointer" />
                <Input v-model="form.countdown_text_color" class="flex-1 text-xs md:text-sm" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { Clock } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

defineProps({
  form: Object,
  countdownDurations: Array,
  showResetButtons: {
    type: Boolean,
    default: false
  },
  resetCountdownColors: Function,
})
</script> 