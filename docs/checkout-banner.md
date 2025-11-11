# Funcionalidade de Banner no Checkout

## Visão Geral

A funcionalidade de banner permite que os vendedores adicionem uma imagem personalizada no topo de seus checkouts, melhorando a apresentação visual e a experiência do cliente.

## Funcionalidades

### 1. Upload de Banner
- **Localização**: Página de criação de checkout (`/seller/checkout`)
- **Formato**: Campo opcional no formulário de criação
- **Formatos aceitos**: JPEG, PNG, JPG, SVG, WEBP
- **Tamanho máximo**: 2MB
- **Preview**: Visualização em tempo real da imagem selecionada

### 2. Exibição do Banner
- **Localização**: Página de checkout público (`/checkout/{id}`)
- **Posicionamento**: Topo da página, acima do formulário de pagamento
- **Dimensões**: Altura fixa de 192px (h-48)
- **Overlay**: Sobreposição escura para melhorar legibilidade do texto
- **Responsivo**: Adapta-se a diferentes tamanhos de tela

### 3. Componente Reutilizável
- **Arquivo**: `resources/js/components/ImageUpload.vue`
- **Props configuráveis**: Label, placeholder, tipos aceitos, tamanho máximo
- **Validação**: Tamanho de arquivo e formato
- **Preview**: Visualização da imagem selecionada
- **Remoção**: Botão para remover imagem selecionada

## Estrutura do Banco de Dados

### Tabela `checkouts`
```sql
ALTER TABLE checkouts ADD COLUMN banner VARCHAR(255) NULL AFTER checkout_template;
```

### Modelo `Checkout`
```php
protected $fillable = [
    'product_id',
    'checkout_template',
    'banner',
];
```

## Armazenamento de Arquivos

### Diretório
- **Caminho**: `storage/app/public/checkouts/banners/`
- **URL pública**: `/storage/checkouts/banners/{filename}`

### Nomenclatura
- Arquivos são armazenados com nomes únicos gerados pelo Laravel
- Exemplo: `checkouts/banners/abc123def456.jpg`

## Validação

### Backend (Controller)
```php
'banner' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg,webp', 'max:2048']
```

### Frontend (Componente)
- Validação de tamanho máximo (2MB)
- Validação de tipos de arquivo aceitos
- Feedback visual para o usuário

## Interface do Usuário

### Formulário de Criação
1. **Campo opcional**: "Banner (Opcional)"
2. **Área de upload**: Drag & drop ou clique para selecionar
3. **Preview**: Visualização da imagem selecionada
4. **Controles**: Botões para trocar ou remover imagem

### Lista de Checkouts
- **Preview do banner**: Miniaturas dos banners nos cards de checkout
- **Indicador visual**: Mostra quando um checkout possui banner

### Página de Checkout
- **Banner em destaque**: Exibição em tamanho grande no topo
- **Texto sobreposto**: Nome e descrição do produto sobre o banner
- **Fallback**: Layout padrão quando não há banner

## Exemplos de Uso

### 1. Criar Checkout com Banner
```javascript
const formData = new FormData();
formData.append('product_id', productId);
formData.append('checkout_template', 'premium');
formData.append('banner', bannerFile);

await router.post('/seller/checkout', formData);
```

### 2. Exibir Banner no Checkout
```vue
<div v-if="checkout.banner" class="w-full h-48 relative overflow-hidden">
    <img 
        :src="`/storage/${checkout.banner}`" 
        :alt="`Banner do checkout ${checkout.product.name}`"
        class="w-full h-full object-cover"
    />
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="absolute bottom-4 left-4 text-white">
        <h3 class="font-semibold text-lg">{{ checkout.product.name }}</h3>
    </div>
</div>
```

## Melhorias Futuras

1. **Editor de imagem**: Recorte e redimensionamento
2. **Templates de banner**: Predefinições para diferentes tipos de produto
3. **Posicionamento**: Opções de posicionamento do texto sobre o banner
4. **Animações**: Transições suaves entre estados
5. **Otimização**: Compressão automática de imagens
6. **CDN**: Integração com CDN para melhor performance

## Troubleshooting

### Problemas Comuns

1. **Imagem não aparece**
   - Verificar se o link simbólico do storage está criado
   - Verificar permissões do diretório de upload

2. **Erro de validação**
   - Verificar formato e tamanho do arquivo
   - Verificar configurações do servidor (upload_max_filesize)

3. **Preview não funciona**
   - Verificar se o FileReader está disponível no navegador
   - Verificar se o arquivo é uma imagem válida

### Comandos Úteis

```bash
# Criar link simbólico do storage
php artisan storage:link

# Executar migração
php artisan migrate

# Limpar cache
php artisan cache:clear
```