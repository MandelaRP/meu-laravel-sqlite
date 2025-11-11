# 🚀 Deploy no Render.com - Guia Rápido

## ✅ Projeto Pronto para Deploy

O projeto já está configurado e pronto para deploy no Render.com!

### 📁 Arquivos de Referência

- **`.env.render`** - Arquivo de referência com todas as variáveis de ambiente necessárias
- **`env.render.example`** - Cópia do arquivo acima (backup)
- **`RENDER_DEPLOY.md`** - Guia completo de deploy

---

## 🚀 Deploy Rápido (3 Passos)

### 1️⃣ Criar App no Render

1. Acesse: https://render.com
2. Clique em **"New +"** → **"Web Service"**
3. Conecte seu repositório GitHub
4. Selecione o repositório: `meu-laravel-sqlite`

### 2️⃣ Configurar Variáveis de Ambiente

No painel do Render, vá em **"Environment"** e adicione as variáveis do arquivo `.env.render`:

**Obrigatórias:**
```
APP_NAME=LuckPay
APP_ENV=production
APP_KEY=base64:GERAR_COM_ARTISAN
APP_DEBUG=false
APP_URL=https://seu-app.onrender.com
DB_CONNECTION=sqlite
DB_DATABASE=/opt/render/project/src/database/database.sqlite
```

**Importante:** Gere a `APP_KEY` executando no terminal local:
```bash
php artisan key:generate --show
```
Copie o valor e cole em `APP_KEY` no Render.

### 3️⃣ Configurar Build e Start Commands

**Build Command:**
```bash
composer install --optimize-autoloader --no-dev && npm install && npm run build && touch database/database.sqlite && chmod 664 database/database.sqlite && php artisan key:generate --force && php artisan migrate --force && php artisan storage:link && chmod -R 775 storage bootstrap/cache && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

**Start Command:**
```bash
php artisan serve --host=0.0.0.0 --port=${PORT}
```

---

## 📋 Checklist

- [x] Arquivo `.env.render` criado e commitado
- [x] Guia de deploy criado (`RENDER_DEPLOY.md`)
- [ ] App criado no Render
- [ ] Variáveis de ambiente configuradas
- [ ] Build Command configurado
- [ ] Start Command configurado
- [ ] Deploy realizado
- [ ] Aplicação testada

---

## 🔧 Configurações Importantes

### SQLite no Render

O projeto está configurado para usar SQLite no Render. O arquivo será criado automaticamente no build.

**Caminho:** `/opt/render/project/src/database/database.sqlite`

### Storage

O link simbólico do storage é criado automaticamente no build.

### Cache

Os caches são gerados automaticamente no build para melhor performance.

---

## 🐛 Troubleshooting

### Erro: "APP_KEY não definida"
- Gere a chave: `php artisan key:generate --show`
- Adicione no Render: `APP_KEY=base64:...`

### Erro: "Database não encontrada"
- O arquivo é criado automaticamente no build
- Verifique se o Build Command está correto

### Erro: "Storage não acessível"
- O link é criado automaticamente no build
- Verifique permissões: `chmod -R 775 storage`

---

## 📚 Documentação Completa

Para mais detalhes, consulte:
- **`RENDER_DEPLOY.md`** - Guia completo
- **`.env.render`** - Todas as variáveis de ambiente

---

## 🎯 Próximos Passos

1. Acesse o Render e crie o app
2. Configure as variáveis de ambiente
3. Faça o deploy
4. Teste a aplicação

**Boa sorte! 🚀**

