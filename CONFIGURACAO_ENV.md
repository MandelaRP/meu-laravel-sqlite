# ⚙️ Configuração do .env para MySQL

## 🔧 Configuração Correta

Seu arquivo `.env` precisa estar configurado assim para conectar ao MySQL:

```env
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

## ❌ O que está ERRADO no seu .env atual:

```env
DB_CONNECTION=sqlite  # ❌ ERRADO - deve ser mysql
# DB_HOST=127.0.0.1    # ❌ COMENTADO - deve estar ativo
# DB_PORT=3306         # ❌ COMENTADO - deve estar ativo
# DB_DATABASE=laravel  # ❌ COMENTADO - deve ser treswebc_gateway
# DB_USERNAME=root     # ❌ COMENTADO - deve ser treswebc_gateway
# DB_PASSWORD=         # ❌ COMENTADO - deve ser MandelaRP123@
```

## ✅ O que você precisa fazer:

1. **Abra o arquivo `.env`** na raiz do projeto

2. **Substitua a seção de banco de dados** por:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=treswebc_gateway
DB_USERNAME=treswebc_gateway
DB_PASSWORD=MandelaRP123@
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

3. **Remova ou comente** a linha do SQLite:
```env
# DB_CONNECTION=sqlite  # Comentado - não usar mais
```

4. **Salve o arquivo**

5. **Limpe o cache de configuração**:
```bash
php artisan config:clear
```

6. **Teste a conexão**:
```bash
php artisan migrate:status
```

## 📝 Arquivo .env Completo (Exemplo)

```env
APP_NAME=LuckPay
APP_ENV=local
APP_KEY=base64:U2bb3zkPzJKDkOrx3uOUQj+D0ruuGc0C6it/XwBSrb0=
APP_DEBUG=true
APP_URL=http://localhost

# Banco de Dados MySQL
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=treswebc_gateway
DB_USERNAME=treswebc_gateway
DB_PASSWORD=MandelaRP123@
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Sessões
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache
CACHE_STORE=database
QUEUE_CONNECTION=database

# ... resto das configurações
```

## ⚠️ IMPORTANTE

- **NÃO** use `DB_CONNECTION=sqlite` - o projeto foi migrado para MySQL
- **SIM** use as credenciais fornecidas pela hospedagem
- **SEMPRE** limpe o cache após alterar o `.env`: `php artisan config:clear`

