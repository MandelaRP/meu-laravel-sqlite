# 📤 Instruções de Upload - Hospedagem Simples

## ✅ Tudo Pronto! Siga estes passos:

### 1️⃣ Preparar Arquivos Localmente

Execute este comando no seu computador (na raiz do projeto):

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2️⃣ Fazer Upload de TODOS os Arquivos

**Faça upload de TODA a pasta do projeto para o servidor.**

A estrutura no servidor deve ficar assim:

```
/ (raiz do servidor ou public_html)
├── index.php              ✅ Já está na raiz
├── .htaccess              ✅ Já está na raiz
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
│   ├── build/
│   ├── images/
│   └── ...
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env                   ⚠️ Configure este arquivo
└── ... (todos os outros arquivos)
```

### 3️⃣ Configurar o .env

**Edite o arquivo `.env` no servidor** e configure:

```env
APP_NAME=LuckPay
APP_ENV=production
APP_KEY=base64:U2bb3zkPzJKDkOrx3uOUQj+D0ruuGc0C6it/XwBSrb0=
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

# Cache e Sessões
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

### 4️⃣ Importar Banco de Dados

1. Acesse o phpMyAdmin da hospedagem
2. Selecione o banco `treswebc_gateway`
3. Vá em **"Importar"**
4. Selecione o arquivo: **`database/mysql_export.sql`**
5. Clique em **"Executar"**

### 5️⃣ Configurar Permissões (se tiver acesso SSH)

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 644 .env
```

### 6️⃣ Testar

Acesse: **https://auraspay.online**

## 🎯 Estrutura Final no Servidor

```
/ (raiz ou public_html)
├── index.php          ← Este arquivo já está pronto!
├── .htaccess          ← Este arquivo já está pronto!
├── app/
├── bootstrap/
├── config/
├── database/
│   └── mysql_export.sql
├── public/
│   └── build/
├── resources/
├── routes/
├── storage/
├── vendor/
└── .env               ← Configure este arquivo!
```

## ⚠️ Importante

- ✅ **NÃO** precisa criar pasta `public_html` separada
- ✅ **NÃO** precisa copiar arquivos manualmente
- ✅ **SIM**, faça upload de TODA a pasta do projeto
- ✅ O `index.php` na raiz já está configurado automaticamente
- ✅ O `.htaccess` na raiz já está pronto

## 🔧 Se Ainda Não Funcionar

1. Verifique se o `.env` está configurado corretamente
2. Verifique se o banco de dados foi importado
3. Verifique os logs: `storage/logs/laravel.log`
4. Certifique-se de que fez upload de TODOS os arquivos (incluindo `vendor/`)

## 📝 Checklist

- [ ] Todos os arquivos foram enviados para o servidor
- [ ] Arquivo `.env` está configurado com as credenciais corretas
- [ ] Banco de dados foi importado via phpMyAdmin
- [ ] `APP_URL` no `.env` está como `https://auraspay.online`
- [ ] Testou acessar `https://auraspay.online`

