# 🔧 Solução: Erro de Storage

## ❌ Erro

```
file_put_contents(...storage/framework/sessions/...): Failed to open stream: No such file or directory
```

## ✅ Solução Rápida

### Opção 1: Script Automático (RECOMENDADO)

1. **Faça upload do arquivo `CRIAR_PASTAS_STORAGE.php` para a raiz do servidor**

2. **Acesse no navegador:**
   ```
   https://auraspay.online/CRIAR_PASTAS_STORAGE.php
   ```

3. **Aguarde a mensagem de sucesso**

4. **DELETE o arquivo `CRIAR_PASTAS_STORAGE.php` por segurança**

### Opção 2: Criar Manualmente (via File Manager)

Crie estas pastas no servidor:

```
storage/
├── app/
│   └── public/
├── framework/
│   ├── cache/
│   │   └── data/
│   ├── sessions/
│   ├── testing/
│   └── views/
└── logs/

bootstrap/
└── cache/
```

### Opção 3: Via SSH (se tiver acesso)

```bash
cd /caminho/do/projeto

mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/testing
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

chmod -R 755 storage bootstrap/cache
```

## 🔄 Após Criar as Pastas

1. **Limpe o cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. **Teste novamente:**
   Acesse: `https://auraspay.online`

## ✅ Verificação

As seguintes pastas devem existir:

- ✅ `storage/app/public/`
- ✅ `storage/framework/cache/data/`
- ✅ `storage/framework/sessions/`
- ✅ `storage/framework/testing/`
- ✅ `storage/framework/views/`
- ✅ `storage/logs/`
- ✅ `bootstrap/cache/`

## 🔒 Permissões

Certifique-se de que as pastas tenham permissão de escrita:
- Pastas: `755` ou `775`
- Arquivos: `644` ou `664`

## 📝 Nota

O código foi atualizado para criar essas pastas automaticamente na próxima versão, mas você precisa criar manualmente agora ou usar o script `CRIAR_PASTAS_STORAGE.php`.

