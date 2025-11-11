# 🔄 Guia de Migração: SQLite → MySQL

## ✅ Migração Concluída!

O projeto foi migrado de SQLite para MySQL e está pronto para hospedagem compartilhada (Hostinger, HostGator, Locaweb).

---

## 📋 O que foi feito

1. ✅ **Banco convertido**: SQLite → MySQL (arquivo `database/mysql_export.sql` gerado)
2. ✅ **Configuração atualizada**: `config/database.php` agora usa MySQL como padrão
3. ✅ **Arquivos de referência criados**: `.env.hostinger` com configurações para hospedagem compartilhada
4. ✅ **Dockerfile atualizado**: Agora usa MySQL em vez de SQLite

---

## 🚀 Passo a Passo para Deploy

### 1️⃣ Preparar Banco de Dados

**No painel da hospedagem (cPanel/Plesk):**

1. Acesse **"MySQL Databases"** ou **"Banco de Dados"**
2. **Crie um novo banco de dados** (ex: `luckpay_db`)
3. **Crie um usuário** para o banco
4. **Anote as credenciais:**
   - Nome do banco
   - Usuário
   - Senha
   - Host (geralmente `localhost`)

### 2️⃣ Importar Dados

**Via phpMyAdmin:**

1. Acesse **phpMyAdmin** no painel da hospedagem
2. Selecione o banco de dados criado
3. Clique em **"Importar"**
4. Selecione o arquivo: **`database/mysql_export.sql`**
5. Clique em **"Executar"**

✅ **Pronto!** Todas as tabelas e dados foram importados.

### 3️⃣ Configurar .env no Servidor

**Copie o arquivo `.env.hostinger` para `.env` no servidor e ajuste:**

```env
APP_NAME=LuckPay
APP_ENV=production
APP_KEY=base64:SUA_CHAVE_AQUI
APP_DEBUG=false
APP_URL=https://seudominio.com.br

# Banco de Dados - Use as credenciais da hospedagem
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nome_do_banco_criado
DB_USERNAME=usuario_criado
DB_PASSWORD=senha_criada
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

**Gerar APP_KEY:**
```bash
php artisan key:generate --show
```

### 4️⃣ Fazer Upload dos Arquivos

**Estrutura recomendada:**

```
/home/usuario/
├── public_html/          (DocumentRoot)
│   ├── index.php
│   ├── .htaccess
│   └── build/           (assets compilados)
│
└── app/                 (raiz do Laravel)
    ├── app/
    ├── config/
    ├── database/
    ├── routes/
    ├── storage/
    ├── vendor/
    └── .env
```

### 5️⃣ Configurar Permissões

**Via SSH ou painel:**

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod 644 .env
```

### 6️⃣ Executar Comandos

**Via SSH:**

```bash
cd /caminho/do/projeto

# Gerar chave (se ainda não fez)
php artisan key:generate --force

# Criar link simbólico do storage
php artisan storage:link

# Limpar e cachear configurações
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7️⃣ Testar

Acesse seu site e verifique se está funcionando!

---

## 🔧 Troubleshooting

### Erro: "Access denied for user"

**Solução:**
- Verifique usuário e senha no `.env`
- Verifique se o usuário tem permissão no banco
- Verifique se o host está correto (geralmente `localhost`)

### Erro: "Unknown database"

**Solução:**
- Verifique se o banco foi criado
- Verifique o nome do banco no `.env`
- Verifique se o usuário tem acesso ao banco

### Erro: "Table doesn't exist"

**Solução:**
- Verifique se importou o arquivo `mysql_export.sql`
- Ou execute: `php artisan migrate --force`

### Erro: "Storage não acessível"

**Solução:**
```bash
php artisan storage:link
chmod -R 755 storage
```

---

## 📊 Arquivos Importantes

- **`database/mysql_export.sql`** - Banco convertido para MySQL (importar no phpMyAdmin)
- **`.env.hostinger`** - Arquivo de referência para hospedagem compartilhada
- **`config/database.php`** - Configuração atualizada para MySQL como padrão

---

## ✅ Checklist Final

- [ ] Banco MySQL criado na hospedagem
- [ ] Arquivo `mysql_export.sql` importado via phpMyAdmin
- [ ] Arquivo `.env` configurado no servidor
- [ ] `APP_KEY` gerada e configurada
- [ ] Permissões configuradas (storage, bootstrap/cache)
- [ ] Link simbólico do storage criado
- [ ] Cache gerado (config, route, view)
- [ ] Site testado e funcionando

---

## 🎯 Próximos Passos

1. Criar banco MySQL na hospedagem
2. Importar `database/mysql_export.sql`
3. Configurar `.env` com credenciais
4. Fazer upload dos arquivos
5. Configurar permissões
6. Testar!

**Projeto 100% pronto para MySQL! 🚀**

