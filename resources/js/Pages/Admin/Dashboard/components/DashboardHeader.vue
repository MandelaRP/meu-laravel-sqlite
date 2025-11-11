<script setup>
import { Button } from '@/components/ui/button';
import { FileText, Trophy } from 'lucide-vue-next';
import { ref } from 'vue';

const activeTab = ref('relatorio');

const setActiveTab = (tab) => {
    activeTab.value = tab;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Título e Descrição -->
        <div class="space-y-1">
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Bem-vindo à Dashboard, Admin</h2>
            <p class="text-sm sm:text-base text-muted-foreground">
                Nesta aba você pode ver os dados gerais da sua plataforma.
            </p>
        </div>

        <!-- Botões de Navegação -->
        <div class="flex gap-3">
            <Button
                :variant="activeTab === 'relatorio' ? 'default' : 'outline'"
                :class="activeTab === 'relatorio' 
                    ? 'bg-primary text-primary-foreground hover:bg-primary/90' 
                    : 'border-border hover:bg-accent hover:text-accent-foreground'"
                @click="setActiveTab('relatorio')"
            >
                <FileText class="h-4 w-4 mr-2" />
                Relatório
            </Button>
            <Button
                :variant="activeTab === 'rankings' ? 'default' : 'outline'"
                :class="activeTab === 'rankings' 
                    ? 'bg-primary text-primary-foreground hover:bg-primary/90' 
                    : 'border-border hover:bg-accent hover:text-accent-foreground'"
                @click="setActiveTab('rankings')"
            >
                <Trophy class="h-4 w-4 mr-2" />
                Rankings
            </Button>
        </div>
    </div>
</template>

