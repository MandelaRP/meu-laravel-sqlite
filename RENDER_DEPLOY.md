# 🚀 Guia de Deploy no Render.com

## 📋 Configuração Básica

### 1. Arquivo .env

Use o arquivo `.env.render` como referência. Copie para `.env` no Render e ajuste:

```bash
# No Render, configure estas variáveis no painel:
APP_NAME=LuckPay
APP_ENV=production
APP_KEY=base64:SUA_CHAVE_AQUI
APP_DEBUG=false
APP_URL=https://seu-app.onrender.com
```

### 2. Gerar APP_KEY

No Render, adicione no **Build Command**:
```bash
php artisan key:generate --force
```

Ou gere localmente e adicione manualmente:
```bash
php artisan key:generate --show
```

---

## 🔧 Configuração no Render

### Build Command

```bash
composer install --optimize-autoloader --no-dev && \
npm install && \
npm run build && \
php artisan key:generate --force && \
php artisan migrate --force && \
php artisan storage:link && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache
```

### Start Command

```bash
php artisan serve --host=0.0.0.0 --port=${PORT}
```

### Environment Variables

Configure no painel do Render:

**Obrigatórias:**
- `APP_NAME=LuckPay`
- `APP_ENV=production`
- `APP_KEY=base64:...` (gerar com artisan)
- `APP_DEBUG=false`
- `APP_URL=https://seu-app.onrender.com`

**Banco de Dados (SQLite):**
- `DB_CONNECTION=sqlite`
- `DB_DATABASE=/opt/render/project/src/database/database.sqlite`

**Ou MySQL (se preferir):**
- `DB_CONNECTION=mysql`
- `DB_HOST=seu-host.render.com`
- `DB_PORT=3306`
- `DB_DATABASE=nome_banco`
- `DB_USERNAME=usuario`
- `DB_PASSWORD=senha`

---

## 📁 Estrutura de Arquivos

### Arquivos Necessários

Certifique-se de que estes arquivos existam:

```
database/
  └── database.sqlite  (criar se não existir)
storage/
  ├── app/
  │   └── public/     (será criado pelo storage:link)
  ├── framework/
  └── logs/
```

### Criar database.sqlite

No Build Command, adicione:
```bash
touch database/database.sqlite
chmod 664 database/database.sqlite
```

---

## 🔐 Permissões

O Render precisa de permissões de escrita em:

- `storage/` (logs, cache, sessions)
- `bootstrap/cache/` (cache de configuração)
- `database/database.sqlite` (se usar SQLite)

Adicione no Build Command:
```bash
chmod -R 775 storage bootstrap/cache
chmod 664 database/database.sqlite
```

---

## 🗄️ Banco de Dados

### Opção 1: SQLite (Mais Simples)

**Vantagens:**
- ✅ Não precisa configurar servidor separado
- ✅ Gratuito
- ✅ Funciona imediatamente

**Desvantagens:**
- ❌ Limitações de performance
- ❌ Não ideal para alta concorrência
- ❌ Backup manual necessário

**Configuração:**
```env
DB_CONNECTION=sqlite
DB_DATABASE=/opt/render/project/src/database/database.sqlite
```

**Importar dados:**
1. Use o comando: `php artisan db:convert-sqlite-to-mysql`
2. Ou copie o arquivo `database.sqlite` diretamente

### Opção 2: MySQL (Recomendado para Produção)

**Vantagens:**
- ✅ Melhor performance
- ✅ Suporta alta concorrência
- ✅ Backups automáticos

**Desvantagens:**
- ❌ Requer serviço separado (pago)
- ❌ Configuração mais complexa

**Configuração:**
1. Crie um serviço PostgreSQL ou MySQL no Render
2. Configure as variáveis de ambiente
3. Execute migrações

---

## 📦 Dependências

### Composer

O Render instala automaticamente via `composer install`.

### NPM

O Render instala automaticamente via `npm install` e compila com `npm run build`.

---

## 🔄 Deploy Automático

### Conectar GitHub

1. No Render, conecte seu repositório GitHub
2. Configure branch (geralmente `main` ou `master`)
3. Render fará deploy automático a cada push

### Deploy Manual

1. No painel do Render, clique em "Manual Deploy"
2. Escolha a branch
3. Clique em "Deploy"

---

## 🐛 Troubleshooting

### Erro: "APP_KEY não definida"

**Solução:**
Adicione no Build Command:
```bash
php artisan key:generate --force
```

### Erro: "Storage não acessível"

**Solução:**
Adicione no Build Command:
```bash
php artisan storage:link
chmod -R 775 storage
```

### Erro: "Database não encontrada"

**Solução (SQLite):**
```bash
touch database/database.sqlite
chmod 664 database/database.sqlite
php artisan migrate --force
```

### Erro: "Permissão negada"

**Solução:**
```bash
chmod -R 775 storage bootstrap/cache
```

### Erro: "Porta não disponível"

**Solução:**
Use `${PORT}` no Start Command:
```bash
php artisan serve --host=0.0.0.0 --port=${PORT}
```

---

## 📊 Monitoramento

### Logs

Acesse logs no painel do Render:
- **Build Logs**: Logs do processo de build
- **Runtime Logs**: Logs da aplicação em execução

### Health Check

Configure no Render:
- **Path**: `/up` (Laravel health check)
- **Interval**: 30 segundos

---

## 💰 Custos

### Plano Gratuito

- ✅ 750 horas/mês
- ✅ Sleep após 15 min de inatividade
- ✅ SQLite incluído
- ✅ Deploy automático

### Plano Pago

- ✅ Sempre online
- ✅ Mais recursos
- ✅ Suporte prioritário

---

## 🎯 Checklist de Deploy

- [ ] Criar conta no Render
- [ ] Conectar repositório GitHub
- [ ] Configurar variáveis de ambiente
- [ ] Configurar Build Command
- [ ] Configurar Start Command
- [ ] Criar database.sqlite (se usar SQLite)
- [ ] Testar deploy
- [ ] Verificar logs
- [ ] Testar aplicação
- [ ] Configurar domínio personalizado (opcional)

---

## 📚 Recursos

- **Documentação Render**: https://render.com/docs
- **Laravel no Render**: https://render.com/docs/deploy-laravel
- **Suporte**: https://render.com/support

---

**Boa sorte com o deploy! 🚀**

