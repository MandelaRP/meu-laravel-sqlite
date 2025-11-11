# 🚀 Guia de Deploy

## 📋 Pré-requisitos

- Banco de dados MySQL criado na hospedagem
- Credenciais de acesso ao banco de dados
- Acesso FTP/SSH ao servidor

## 🔧 Configuração do Banco de Dados

### 1. Criar Banco de Dados

No painel da hospedagem (cPanel/Plesk):
1. Acesse **"MySQL Databases"** ou **"Banco de Dados"**
2. Crie um novo banco de dados
3. Crie um usuário e associe ao banco
4. Anote as credenciais

### 2. Importar Dados

Via phpMyAdmin:
1. Acesse **phpMyAdmin**
2. Selecione o banco de dados criado
3. Clique em **"Importar"**
4. Selecione o arquivo: **`database/mysql_export.sql`**
5. Clique em **"Executar"**

## ⚙️ Configuração do .env

Configure o arquivo `.env` no servidor:

```env
APP_NAME=LuckPay
APP_ENV=production
APP_KEY=base64:SUA_CHAVE_AQUI
APP_DEBUG=false
APP_URL=https://seudominio.com.br

# Banco de Dados MySQL
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario_banco
DB_PASSWORD=senha_banco
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Cache e Sessões
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

**Gerar APP_KEY:**
```bash
php artisan key:generate
```

## 📤 Upload dos Arquivos

1. Faça upload de todos os arquivos para o servidor
2. Configure o DocumentRoot para a pasta `public/`
3. Execute as migrações (se necessário):
```bash
php artisan migrate --force
```

## ✅ Verificação

Teste a conexão com o banco:
```bash
php artisan migrate:status
```

