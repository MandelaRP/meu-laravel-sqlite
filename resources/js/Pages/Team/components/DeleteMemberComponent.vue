<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from '@/components/ui/toast/use-toast';
import Button from '@/components/ui/button/Button.vue';
import { LoaderCircle } from 'lucide-vue-next';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';

const props = defineProps<{
  member: {
    id: number;
    name: string;
  };
}>();

const { toast } = useToast();
const open = ref(false);
const confirmDelete = ref(false);

const form = useForm({});

const submit = () => {
  form.delete(route('team.destroy', props.member.id), {
    onSuccess: () => {
      toast({
        title: 'Membro excluído com sucesso',
      });
      open.value = false;
    },
    onError: (error) => {
      console.error(error);
      toast({
        title: 'Erro ao excluir membro',
        description: 'Ocorreu um erro ao tentar excluir o membro.',
        variant: 'destructive',
      });
    },
  });
};
</script>

<template>
  <Dialog :open="open">
    <DialogTrigger as-child>
      <button @click="open = true" 
        class="rounded-full p-2 text-gray-500 hover:bg-gray-100 focus:outline-none dark:text-gray-400 dark:hover:bg-gray-700 hover:text-red-500 dark:hover:text-red-400">
        <Trash2 class="h-4 w-4" />
      </button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Excluir membro</DialogTitle>
        <DialogDescription>
          Tem certeza que deseja excluir o membro <strong>{{ props.member.name }}</strong>?
          Esta ação não pode ser desfeita.
        </DialogDescription>
      </DialogHeader>

      <div class="py-4">
        <div class="flex items-center space-x-2">
          <input class="w-4 h-4 rounded-md border border-gray-300" id="confirm-delete"
                 v-model="confirmDelete" type="checkbox" />
          <label for="confirm-delete" class="text-sm font-medium">
            Sim, desejo excluir este membro
          </label>
        </div>
      </div>

      <DialogFooter>
        <Button @click="open = false" variant="outline">Cancelar</Button>
        <Button @click="submit" type="submit" variant="destructive" :disabled="!confirmDelete || form.processing">
          <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
          Excluir
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template> 