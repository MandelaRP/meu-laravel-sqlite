<script setup>
import { LayoutGrid, Plus, X, Check } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

const props = defineProps({
  form: Object,
  newFieldKey: String,
  newFieldLabel: String,
  toggleAllFields: Function,
  addCustomField: Function,
  removeField: Function,
})

const emit = defineEmits(['update:newFieldKey', 'update:newFieldLabel'])

const updateNewFieldKey = (value) => {
  emit('update:newFieldKey', value)
}

const updateNewFieldLabel = (value) => {
  emit('update:newFieldLabel', value)
}

const toggleRequired = (key) => {
  props.form.form_fields_config[key].required = !props.form.form_fields_config[key].required
}

const toggleVisible = (key) => {
  props.form.form_fields_config[key].visible = !props.form.form_fields_config[key].visible
}
</script> 

<template>
  <Card>
    <CardHeader class="px-3 md:px-6">
      <CardTitle class="flex items-center gap-2 text-sm md:text-base">
        <LayoutGrid class="h-4 w-4 md:h-5 md:w-5" />
        Campos do Formulário
      </CardTitle>
      <CardDescription class="text-xs md:text-sm">
        Configure quais campos serão exibidos no formulário de checkout e quais são obrigatórios
      </CardDescription>
    </CardHeader>
    <CardContent class="px-3 md:px-6">
      <div class="space-y-4 md:space-y-6">
        <div class="space-y-3 md:space-y-4">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <h4 class="font-medium text-xs md:text-sm">Campos Disponíveis</h4>
            <div class="flex items-center gap-2">
              <Button @click="toggleAllFields(true)" variant="outline" size="sm" class="text-xs">
                Marcar Todos
              </Button>
              <Button @click="toggleAllFields(false)" variant="outline" size="sm" class="text-xs">
                Desmarcar Todos
              </Button>
            </div>
          </div>
          <div class="space-y-2 md:space-y-3">
            <div v-for="(field, key) in form.form_fields_config" :key="key"
              class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-2 md:p-3 border rounded-lg gap-2 md:gap-3"
              :class="field.required ? 'border-green-200 bg-green-50/30' : 'border-gray-200'">
              <div class="flex items-center gap-2 md:gap-3 flex-1 min-w-0">
                <div class="flex-1 min-w-0">
                  <label class="font-medium text-foreground cursor-pointer text-xs md:text-sm">
                    {{ field.label }}
                    <span v-if="field.required" class="ml-1 text-green-600 text-xs">(Obrigatório)</span>
                  </label>
                  <p class="text-xs text-muted-foreground truncate">{{ key }}</p>
                </div>
              </div>
              <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 md:gap-3 w-full sm:w-auto">
                  <Button 
                    type="button"
                    @click="toggleRequired(key)"
                    :variant="form.form_fields_config[key].required ? 'default' : 'outline'"
                    size="sm"
                    class="text-xs h-8"
                  >
                    <Check v-if="form.form_fields_config[key].required" class="h-3 w-3 mr-1" />
                    Obrigatório
                  </Button>
                  <Button 
                    type="button"
                    @click="toggleVisible(key)"
                    :variant="form.form_fields_config[key].visible ? 'default' : 'outline'"
                    size="sm"
                    class="text-xs h-8"
                  >
                    <Check v-if="form.form_fields_config[key].visible" class="h-3 w-3 mr-1" />
                    Visível
                  </Button>
                <button @click="removeField(key)" class="p-1 text-muted-foreground hover:text-destructive">
                  <X class="h-3 w-3 md:h-4 md:w-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Adicionar Campo Personalizado -->
          <div class="p-2 md:p-3 border-2 border-dashed border-muted-foreground/20 rounded-lg">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 md:gap-3">
              <div class="flex-1 w-full">
                <input 
                  :value="newFieldKey" 
                  @input="updateNewFieldKey($event.target.value)"
                  placeholder="Chave do campo (ex: company)"
                  class="w-full text-xs md:text-sm px-2 py-1 border rounded" 
                />
              </div>
              <div class="flex-1 w-full">
                <input 
                  :value="newFieldLabel" 
                  @input="updateNewFieldLabel($event.target.value)"
                  placeholder="Label do campo (ex: Empresa)"
                  class="w-full text-xs md:text-sm px-2 py-1 border rounded" 
                />
              </div>
              <button @click="addCustomField" :disabled="!newFieldKey || !newFieldLabel"
                class="p-2 bg-primary text-primary-foreground rounded hover:bg-primary/90 disabled:opacity-50">
                <Plus class="h-3 w-3 md:h-4 md:w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>