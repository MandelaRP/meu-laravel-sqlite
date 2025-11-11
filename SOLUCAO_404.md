# 🔧 Solução Rápida: Erro 404 em Hospedagem Compartilhada

## 🎯 Problema

Você está recebendo **404 Not Found** ao acessar `https://auraspay.online/`

## ✅ Solução Rápida

### Passo 1: Verificar Estrutura

A estrutura mais comum em hospedagem compartilhada é:

```
/home/usuario/
├── app/              (Laravel - FORA do public_html)
│   ├── app/
│   ├── vendor/
│   ├── .env
│   └── ...
│
└── public_html/      (DocumentRoot - aqui ficam os arquivos públicos)
    ├── index.php
    ├── .htaccess
    └── build/
```

### Passo 2: Copiar Arquivos para public_html/

**1. Copie e renomeie o index.php:**
```bash
# No servidor:
cp app/public/index-shared-hosting.php public_html/index.php
```

**2. Edite `public_html/index.php` e ajuste o caminho:**
```php
// Encontre esta linha (linha 26):
$laravelPath = dirname(__DIR__);

// Substitua por (ajuste o caminho do seu servidor):
$laravelPath = '/home/usuario/app';
// OU
$laravelPath = '/home/treswebc/app';  // ajuste conforme seu usuário
```

**3. Copie o .htaccess:**
```bash
cp .htaccess-shared-hosting public_html/.htaccess
```

**4. Copie a pasta build:**
```bash
cp -r app/public/build public_html/build
```

**5. Copie outros arquivos:**
```bash
cp app/public/favicon.ico public_html/
cp app/public/robots.txt public_html/
cp -r app/public/images public_html/
```

### Passo 3: Verificar .env

Certifique-se de que o `.env` está em `/home/usuario/app/.env` com:

```env
APP_URL=https://auraspay.online
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=treswebc_gateway
DB_USERNAME=treswebc_gateway
DB_PASSWORD=MandelaRP123@
```

### Passo 4: Limpar Cache

Via SSH ou terminal do cPanel:

```bash
cd /home/usuario/app
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Passo 5: Testar

Acesse: `https://auraspay.online`

## 🔍 Como Descobrir o Caminho Correto

**Via cPanel File Manager:**
1. Abra o File Manager
2. Veja o caminho completo na barra de endereço
3. Exemplo: `/home/treswebc/public_html` → Laravel está em `/home/treswebc/app`

**Via SSH:**
```bash
pwd  # Mostra o diretório atual
```

## ⚠️ Importante

- O Laravel **NÃO** deve estar dentro de `public_html/`
- Apenas os arquivos de `public/` devem estar em `public_html/`
- O `.env` deve estar na raiz do Laravel (fora do `public_html/`)

## 📞 Se ainda não funcionar

1. Verifique os logs: `app/storage/logs/laravel.log`
2. Verifique se o PHP está funcionando (crie `public_html/test.php` com `<?php phpinfo(); ?>`)
3. Verifique as permissões das pastas

