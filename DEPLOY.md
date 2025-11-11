# Guia de Deploy - LuckPay

## 📋 Índice
1. [Hospedagem Compartilhada (Hostinger, HostGator, Locaweb)](#hospedagem-compartilhada)
2. [Hospedagem Recomendada (VPS/Cloud)](#hospedagem-recomendada)
3. [Checklist de Requisitos](#checklist-de-requisitos)

---

## 🏠 Hospedagem Compartilhada

### ⚠️ Limitações Importantes

**NÃO é possível apenas subir arquivos e banco SQL diretamente** porque:

1. **Dependências do Composer**: O projeto precisa instalar dependências PHP via Composer
2. **Build dos Assets**: Precisa compilar JavaScript/TypeScript com Node.js e Vite
3. **Permissões**: Precisa de permissões de escrita em `storage/` e `bootstrap/cache/`
4. **Comandos Artisan**: Precisa executar `php artisan` para configurações
5. **Link Simbólico**: Precisa criar link simbólico `storage` → `public/storage`
6. **Cron Jobs**: Precisa configurar agendamento de tarefas
7. **Queue Worker**: Pode precisar de processamento de filas em background

### ✅ O que É Possível em Hospedagem Compartilhada

Com algumas adaptações e acesso SSH, é possível, mas requer:

- **Acesso SSH** (nem todas as hospedagens compartilhadas oferecem)
- **Composer instalado** no servidor
- **Node.js e NPM** disponíveis
- **PHP 8.2+** com extensões necessárias
- **Permissões de escrita** em pastas específicas

### 📝 Passo a Passo para Hospedagem Compartilhada

#### 1. Preparação Local

```bash
# 1. Instalar dependências
composer install --optimize-autoloader --no-dev
npm install
npm run build

# 2. Gerar arquivos de produção
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

#### 2. Estrutura de Pastas no Servidor

Na hospedagem compartilhada, geralmente você tem:
```
/home/usuario/public_html/  (ou /www/ ou /htdocs/)
```

**Estrutura recomendada:**
```
/home/usuario/
├── public_html/          (DocumentRoot - apenas arquivos públicos)
│   ├── index.php
│   ├── .htaccess
│   ├── build/           (assets compilados)
│   └── storage -> ../app/storage/app/public (link simbólico)
│
├── app/                 (raiz do Laravel - FORA do public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/         (cópia para public_html)
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── artisan
```

#### 3. Upload dos Arquivos

**Arquivos a enviar:**
- ✅ Toda a estrutura do Laravel (exceto `node_modules`, `.git`, `tests`)
- ✅ Pasta `vendor/` (após `composer install`)
- ✅ Pasta `public/build/` (após `npm run build`)
- ✅ Arquivo `.env` (criar manualmente no servidor)

**Arquivos a NÃO enviar:**
- ❌ `node_modules/`
- ❌ `.git/`
- ❌ `tests/`
- ❌ `storage/logs/*.log`
- ❌ `storage/framework/cache/*`
- ❌ `storage/framework/sessions/*`
- ❌ `storage/framework/views/*`

#### 4. Configuração no Servidor

**A. Criar arquivo `.env` no servidor:**
```env
APP_NAME=LuckPay
APP_ENV=production
APP_KEY=base64:SUA_CHAVE_AQUI
APP_DEBUG=false
APP_URL=https://seudominio.com.br

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario_banco
DB_PASSWORD=senha_banco

# ... outras configurações
```

**B. Ajustar `public/index.php` para apontar para a raiz:**
```php
// Se Laravel está em /home/usuario/app/
// e public_html aponta para /home/usuario/public_html/
// Ajustar os caminhos:
require __DIR__.'/../app/vendor/autoload.php';
$app = require_once __DIR__.'/../app/bootstrap/app.php';
```

**C. Criar link simbólico:**
```bash
cd /home/usuario/public_html
ln -s ../app/storage/app/public storage
```

**D. Configurar permissões:**
```bash
chmod -R 755 /home/usuario/app/storage
chmod -R 755 /home/usuario/app/bootstrap/cache
```

**E. Configurar Cron Job:**
No painel da hospedagem, adicionar:
```
* * * * * cd /home/usuario/app && php artisan schedule:run >> /dev/null 2>&1
```

#### 5. Configurar .htaccess

Criar/ajustar `.htaccess` em `public_html/`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ index.php [L]
</IfModule>
```

---

## ☁️ Hospedagem Recomendada (VPS/Cloud)

### 🎯 Por que VPS/Cloud é Melhor

Para um projeto Laravel moderno como este, **recomendo fortemente** usar:

1. **VPS (Virtual Private Server)**
   - DigitalOcean, Linode, Vultr, Contabo
   - Custo: R$ 20-50/mês
   - Controle total do servidor

2. **Cloud Platforms**
   - **Laravel Forge** (recomendado) - R$ 12/mês + servidor
   - **Ploi** - Alternativa ao Forge
   - **Railway** - Deploy automático
   - **Render** - Deploy simples
   - **Fly.io** - Global edge deployment

3. **Hospedagem Laravel Especializada**
   - **Laravel Vapor** (AWS Serverless)
   - **Laravel Shift** (gerenciamento)

### 🚀 Opção Mais Simples: Laravel Forge + DigitalOcean

**Custo estimado:** ~R$ 30-40/mês

**Vantagens:**
- ✅ Deploy automático via Git
- ✅ SSL automático (Let's Encrypt)
- ✅ Configuração de servidor automática
- ✅ Queue workers configurados
- ✅ Cron jobs configurados
- ✅ Backups automáticos
- ✅ Monitoramento

**Passo a passo:**
1. Criar conta no DigitalOcean (droplet $6/mês)
2. Conectar ao Laravel Forge
3. Conectar repositório Git
4. Configurar variáveis de ambiente
5. Deploy automático!

---

## 📋 Checklist de Requisitos

### Requisitos Mínimos do Servidor

- ✅ **PHP 8.2 ou superior**
- ✅ **Composer** instalado
- ✅ **Node.js 18+** e **NPM**
- ✅ **MySQL 5.7+** ou **MariaDB 10.3+**
- ✅ **Extensões PHP:**
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
  - GD ou Imagick (para processamento de imagens)
  - Zip

### Configurações Necessárias

- ✅ **Permissões de escrita** em:
  - `storage/`
  - `bootstrap/cache/`
  
- ✅ **Link simbólico** criado:
  - `public/storage` → `storage/app/public`

- ✅ **Variáveis de ambiente** configuradas no `.env`

- ✅ **Cron job** configurado:
  ```
  * * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
  ```

- ✅ **Queue worker** (se usar filas):
  ```
  php artisan queue:work --daemon
  ```

---

## 🔧 Scripts de Deploy

Criei scripts auxiliares para facilitar o deploy. Veja a pasta `scripts/`.

---

## 📞 Suporte

Se precisar de ajuda com o deploy, verifique:
1. Logs do Laravel: `storage/logs/laravel.log`
2. Logs do servidor web (Apache/Nginx)
3. Permissões de arquivos
4. Configuração do `.env`

