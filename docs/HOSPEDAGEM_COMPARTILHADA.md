# 🌐 Configuração para Hospedagem Compartilhada

## ❌ Erro 404 - Solução

Se você está recebendo **404 Not Found**, siga estes passos:

## 📁 Estrutura de Arquivos

### Opção 1: DocumentRoot aponta para `public/` (RECOMENDADO)

Se você pode alterar o DocumentRoot no cPanel:

1. **Estrutura no servidor:**
```
/home/usuario/
├── app/                    (raiz do Laravel)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/             (esta pasta será o DocumentRoot)
│   │   ├── index.php
│   │   ├── .htaccess
│   │   └── build/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   └── .env
```

2. **No cPanel:**
   - Vá em **"Domínios"** → **"Gerenciar"**
   - Altere o **DocumentRoot** para: `/home/usuario/app/public`
   - Salve

3. **Arquivos necessários:**
   - ✅ `public/index.php` (já existe)
   - ✅ `public/.htaccess` (já existe)

### Opção 2: DocumentRoot é `public_html/` (MAIS COMUM)

Se você NÃO pode alterar o DocumentRoot:

1. **Estrutura no servidor:**
```
/home/usuario/
├── app/                    (raiz do Laravel - FORA do public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   └── .env
│
└── public_html/            (DocumentRoot padrão)
    ├── index.php           (copiar de public/index-shared-hosting.php)
    ├── .htaccess           (copiar de .htaccess-shared-hosting)
    └── build/              (copiar de public/build/)
```

2. **Passos:**

   **a) Copie o index.php adaptado:**
   ```bash
   # No servidor, copie:
   public/index-shared-hosting.php → public_html/index.php
   ```

   **b) Edite o `public_html/index.php` e ajuste o caminho:**
   ```php
   // Se Laravel está em /home/usuario/app/
   $laravelPath = '/home/usuario/app';
   ```

   **c) Copie o .htaccess:**
   ```bash
   # Copie:
   .htaccess-shared-hosting → public_html/.htaccess
   ```

   **d) Copie a pasta build:**
   ```bash
   # Copie toda a pasta:
   public/build/ → public_html/build/
   ```

   **e) Copie outros arquivos públicos:**
   ```bash
   public/favicon.ico → public_html/favicon.ico
   public/robots.txt → public_html/robots.txt
   public/images/ → public_html/images/
   ```

## ⚙️ Configuração do .env

Certifique-se de que o `.env` está na raiz do Laravel (pasta `app/`):

```env
APP_NAME=LuckPay
APP_ENV=production
APP_KEY=base64:SUA_CHAVE_AQUI
APP_DEBUG=false
APP_URL=https://auraspay.online

# Banco de Dados MySQL
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=treswebc_gateway
DB_USERNAME=treswebc_gateway
DB_PASSWORD=MandelaRP123@
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

## 🔧 Permissões de Pastas

Execute no servidor (via SSH ou File Manager):

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 644 .env
```

## ✅ Verificação

1. **Teste a URL:** `https://auraspay.online`
2. **Verifique os logs:** `storage/logs/laravel.log`
3. **Teste uma rota específica:** `https://auraspay.online/login`

## 🐛 Troubleshooting

### Ainda recebendo 404?

1. **Verifique se o `.htaccess` está no lugar certo:**
   - Se DocumentRoot = `public/` → `.htaccess` deve estar em `public/.htaccess`
   - Se DocumentRoot = `public_html/` → `.htaccess` deve estar em `public_html/.htaccess`

2. **Verifique se o `mod_rewrite` está ativo:**
   - No cPanel, verifique se o Apache tem `mod_rewrite` habilitado

3. **Verifique o caminho no `index.php`:**
   - Se usar `index-shared-hosting.php`, certifique-se de que `$laravelPath` está correto

4. **Verifique os logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

5. **Teste se o PHP está funcionando:**
   - Crie `public_html/test.php` com `<?php phpinfo(); ?>`
   - Acesse `https://auraspay.online/test.php`
   - Se funcionar, o problema é na configuração do Laravel

## 📝 Checklist Rápido

- [ ] `.env` configurado corretamente
- [ ] `index.php` no DocumentRoot
- [ ] `.htaccess` no DocumentRoot
- [ ] Pasta `build/` copiada para DocumentRoot
- [ ] Permissões corretas (755 para pastas, 644 para arquivos)
- [ ] `APP_URL` no `.env` está correto
- [ ] Banco de dados importado
- [ ] Cache limpo: `php artisan config:clear`

