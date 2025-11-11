# 🚀 Guia Completo de Deploy - LuckPay

## 📌 Resumo Executivo

**Pergunta:** É possível hospedar apenas subindo arquivos e banco SQL?

**Resposta:** ❌ **NÃO diretamente**. Laravel requer:
- Instalação de dependências (Composer)
- Compilação de assets (Node.js/NPM)
- Configurações via terminal
- Permissões específicas

**Solução:** Este guia fornece scripts e instruções para adaptar o projeto.

---

## 🎯 Opções de Hospedagem

### 1️⃣ Hospedagem Compartilhada (Hostinger, HostGator, Locaweb)

#### ✅ Vantagens
- Baixo custo (R$ 10-30/mês)
- Gerenciamento simples
- Suporte incluído

#### ❌ Desvantagens
- Requer acesso SSH (nem todas oferecem)
- Limitações de recursos
- Configuração mais complexa
- Pode não ter todas as extensões PHP necessárias
- Node.js pode não estar disponível

#### 📋 Requisitos Mínimos
- ✅ Acesso SSH
- ✅ PHP 8.2+
- ✅ Composer instalado
- ✅ Node.js 18+ e NPM
- ✅ MySQL/MariaDB
- ✅ Permissões de escrita

#### 🔧 Processo de Deploy

**Passo 1: Preparação Local**
```bash
# Executar script de preparação
chmod +x scripts/prepare-for-shared-hosting.sh
./scripts/prepare-for-shared-hosting.sh
```

**Passo 2: Criar Pacote**
```bash
chmod +x scripts/create-deploy-package.sh
./scripts/create-deploy-package.sh
```

**Passo 3: Upload**
- Fazer upload do arquivo `.zip` gerado
- Extrair no servidor

**Passo 4: Configuração no Servidor**
```bash
# Conectar via SSH e executar:
cd /caminho/do/projeto
chmod +x scripts/deploy-shared-hosting.sh
./scripts/deploy-shared-hosting.sh
```

**Passo 5: Configurar Estrutura**

**Opção A: Laravel na raiz, public_html aponta para public/**
```
/home/usuario/
├── app/              (raiz Laravel)
│   ├── app/
│   ├── public/      (DocumentRoot)
│   └── ...
└── public_html -> app/public (link simbólico)
```

**Opção B: Laravel fora, public_html copiado**
```
/home/usuario/
├── app/              (raiz Laravel)
│   ├── app/
│   ├── public/
│   └── ...
└── public_html/      (copiar conteúdo de app/public/)
    ├── index.php     (ajustado - ver public/index-shared-hosting.php)
    └── .htaccess
```

---

### 2️⃣ VPS (Virtual Private Server) ⭐ RECOMENDADO

#### ✅ Vantagens
- Controle total
- Recursos dedicados
- Flexibilidade total
- Custo-benefício excelente

#### 💰 Custo
- **DigitalOcean**: $6-12/mês (~R$ 30-60)
- **Vultr**: $6-12/mês
- **Linode**: $5-10/mês
- **Contabo**: €4.99/mês (~R$ 25)

#### 🛠️ Configuração Manual

**1. Provisionar Servidor**
- Ubuntu 22.04 LTS
- 1GB RAM mínimo (2GB recomendado)
- 1 vCPU
- 25GB SSD

**2. Instalar Stack LEMP**
```bash
# Atualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Nginx
sudo apt install nginx -y

# Instalar MySQL
sudo apt install mysql-server -y

# Instalar PHP 8.2
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
    php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath -y

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

**3. Configurar Nginx**
```nginx
server {
    listen 80;
    server_name seudominio.com.br;
    root /var/www/luckpay/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**4. Deploy do Projeto**
```bash
# Clonar repositório ou fazer upload
cd /var/www
git clone seu-repositorio luckpay
# OU fazer upload via FTP/SFTP

cd luckpay

# Instalar dependências
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Configurar
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link

# Otimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissões
sudo chown -R www-data:www-data /var/www/luckpay
sudo chmod -R 755 /var/www/luckpay/storage
sudo chmod -R 755 /var/www/luckpay/bootstrap/cache
```

**5. Configurar Cron**
```bash
sudo crontab -e
# Adicionar:
* * * * * cd /var/www/luckpay && php artisan schedule:run >> /dev/null 2>&1
```

**6. Configurar Queue Worker (Supervisor)**
```bash
sudo apt install supervisor -y

# Criar arquivo: /etc/supervisor/conf.d/luckpay-worker.conf
[program:luckpay-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/luckpay/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/luckpay/storage/logs/worker.log
stopwaitsecs=3600
```

---

### 3️⃣ Laravel Forge ⭐⭐ MAIS RECOMENDADO

#### ✅ Vantagens
- Deploy automático via Git
- SSL automático (Let's Encrypt)
- Configuração automática
- Queue workers configurados
- Cron jobs configurados
- Backups automáticos
- Monitoramento
- Muito fácil de usar

#### 💰 Custo
- **Forge**: $12/mês (~R$ 60)
- **Servidor DigitalOcean**: $6/mês (~R$ 30)
- **Total**: ~R$ 90/mês

#### 🚀 Processo

**1. Criar Conta**
- Acesse: https://forge.laravel.com
- Conecte com GitHub/GitLab

**2. Provisionar Servidor**
- Escolha DigitalOcean, AWS, Linode, etc.
- Forge cria e configura automaticamente

**3. Conectar Repositório**
- Conecte seu repositório Git
- Configure branch (main/master)

**4. Configurar Deploy**
- Variáveis de ambiente
- Comandos de deploy
- Tudo via interface web!

**5. Deploy Automático**
- Push para Git = Deploy automático
- Zero configuração manual

---

### 4️⃣ Cloud Platforms (Alternativas Modernas)

#### Railway.app
- ✅ Deploy automático
- ✅ SSL incluído
- ✅ Custo: ~$5-20/mês
- ✅ Muito simples

#### Render.com
- ✅ Deploy automático
- ✅ SSL incluído
- ✅ Plano gratuito disponível
- ✅ Fácil configuração

#### Fly.io
- ✅ Edge deployment global
- ✅ Escalável
- ✅ Custo baseado em uso

---

## 📊 Comparação de Opções

| Opção | Custo/Mês | Dificuldade | Recomendado |
|-------|-----------|-------------|-------------|
| Hospedagem Compartilhada | R$ 10-30 | ⭐⭐⭐⭐ Difícil | ⚠️ Só se tiver SSH |
| VPS Manual | R$ 25-60 | ⭐⭐⭐ Média | ✅ Sim |
| Laravel Forge | R$ 90+ | ⭐ Muito Fácil | ✅✅ Sim |
| Railway/Render | R$ 30-100 | ⭐⭐ Fácil | ✅ Sim |

---

## 🎯 Recomendação Final

### Para Iniciantes
**Laravel Forge + DigitalOcean** (~R$ 90/mês)
- Mais fácil
- Menos trabalho
- Mais confiável

### Para Intermediários
**VPS Manual** (R$ 30-60/mês)
- Mais controle
- Mais barato
- Requer conhecimento técnico

### Para Orçamento Limitado
**Hospedagem Compartilhada** (R$ 10-30/mês)
- Mais barato
- Mais trabalho
- Requer acesso SSH

---

## 📝 Checklist de Deploy

### Antes do Deploy
- [ ] Testar localmente
- [ ] Executar testes
- [ ] Verificar variáveis de ambiente
- [ ] Compilar assets (`npm run build`)
- [ ] Instalar dependências (`composer install --no-dev`)

### Durante o Deploy
- [ ] Fazer upload dos arquivos
- [ ] Configurar `.env`
- [ ] Executar migrações
- [ ] Criar link simbólico do storage
- [ ] Configurar permissões
- [ ] Configurar cron job
- [ ] Configurar queue worker (se necessário)

### Após o Deploy
- [ ] Testar aplicação
- [ ] Verificar logs
- [ ] Configurar SSL
- [ ] Configurar backups
- [ ] Monitorar performance

---

## 🔧 Arquivos de Configuração Criados

1. **`DEPLOY.md`** - Guia geral
2. **`GUIA_DEPLOY_COMPLETO.md`** - Este arquivo (guia detalhado)
3. **`scripts/prepare-for-shared-hosting.sh`** - Preparação local
4. **`scripts/deploy-shared-hosting.sh`** - Configuração no servidor
5. **`scripts/create-deploy-package.sh`** - Criar pacote ZIP
6. **`public/index-shared-hosting.php`** - Index.php adaptado
7. **`.htaccess-shared-hosting`** - .htaccess para compartilhada

---

## 🆘 Suporte e Troubleshooting

### Problemas Comuns

**1. Erro 500**
- Verificar permissões de `storage/` e `bootstrap/cache/`
- Verificar logs: `storage/logs/laravel.log`
- Verificar `.env` configurado corretamente

**2. Assets não carregam**
- Verificar se `npm run build` foi executado
- Verificar se pasta `public/build/` existe
- Verificar permissões

**3. Erro de banco de dados**
- Verificar credenciais no `.env`
- Verificar se banco foi criado
- Verificar se migrações foram executadas

**4. Link simbólico não funciona**
- Executar: `php artisan storage:link`
- Verificar permissões
- Em alguns servidores, usar link relativo

---

## 📞 Próximos Passos

1. Escolha sua opção de hospedagem
2. Execute os scripts de preparação
3. Siga o guia específico da opção escolhida
4. Teste tudo antes de colocar em produção

**Boa sorte com o deploy! 🚀**

