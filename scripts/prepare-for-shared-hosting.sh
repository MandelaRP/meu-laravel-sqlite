#!/bin/bash

# Script para preparar o projeto Laravel para hospedagem compartilhada
# Uso: ./scripts/prepare-for-shared-hosting.sh

echo "🚀 Preparando projeto para hospedagem compartilhada..."

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verificar se está na raiz do projeto
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Erro: Execute este script na raiz do projeto Laravel${NC}"
    exit 1
fi

echo -e "${YELLOW}📦 Instalando dependências do Composer (produção)...${NC}"
composer install --optimize-autoloader --no-dev --no-interaction

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Erro ao instalar dependências do Composer${NC}"
    exit 1
fi

echo -e "${YELLOW}📦 Instalando dependências do NPM...${NC}"
npm install --production

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Erro ao instalar dependências do NPM${NC}"
    exit 1
fi

echo -e "${YELLOW}🔨 Compilando assets para produção...${NC}"
npm run build

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Erro ao compilar assets${NC}"
    exit 1
fi

echo -e "${YELLOW}🗑️  Limpando arquivos desnecessários...${NC}"

# Remover node_modules (não precisa no servidor)
rm -rf node_modules

# Limpar cache de desenvolvimento
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Limpar logs antigos
find storage/logs -name "*.log" -type f -delete

# Limpar cache do framework
rm -rf storage/framework/cache/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*

# Manter estrutura mas limpar conteúdo
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
touch storage/framework/cache/data/.gitkeep
touch storage/framework/sessions/.gitkeep
touch storage/framework/views/.gitkeep

echo -e "${YELLOW}📝 Criando arquivo .env.example atualizado...${NC}"
if [ ! -f ".env.example" ]; then
    cp .env .env.example 2>/dev/null || echo "# Arquivo .env.example será criado manualmente"
fi

echo -e "${GREEN}✅ Preparação concluída!${NC}"
echo ""
echo -e "${YELLOW}📋 Próximos passos:${NC}"
echo "1. Compacte os arquivos (exceto node_modules, .git, tests)"
echo "2. Faça upload para o servidor"
echo "3. Configure o arquivo .env no servidor"
echo "4. Execute no servidor:"
echo "   - php artisan key:generate"
echo "   - php artisan migrate"
echo "   - php artisan storage:link"
echo "   - php artisan config:cache"
echo "   - php artisan route:cache"
echo "   - php artisan view:cache"
echo ""
echo -e "${GREEN}✨ Pronto para deploy!${NC}"

