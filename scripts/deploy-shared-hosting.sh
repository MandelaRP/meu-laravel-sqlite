#!/bin/bash

# Script para executar no servidor de hospedagem compartilhada
# Execute este script APÓS fazer upload dos arquivos

echo "🚀 Configurando Laravel no servidor..."

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Verificar se está na raiz do projeto
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Erro: Execute este script na raiz do projeto Laravel${NC}"
    exit 1
fi

# Verificar se .env existe
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚠️  Arquivo .env não encontrado${NC}"
    echo -e "${YELLOW}📝 Copiando .env.example para .env...${NC}"
    if [ -f ".env.example" ]; then
        cp .env.example .env
    else
        echo -e "${RED}❌ Arquivo .env.example não encontrado. Crie o arquivo .env manualmente.${NC}"
        exit 1
    fi
fi

echo -e "${YELLOW}🔑 Gerando chave da aplicação...${NC}"
php artisan key:generate --force

echo -e "${YELLOW}🗄️  Executando migrações...${NC}"
php artisan migrate --force

echo -e "${YELLOW}🔗 Criando link simbólico do storage...${NC}"
php artisan storage:link

echo -e "${YELLOW}📦 Otimizando aplicação...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo -e "${YELLOW}🔐 Configurando permissões...${NC}"
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo -e "${GREEN}✅ Configuração concluída!${NC}"
echo ""
echo -e "${YELLOW}📋 Não esqueça de:${NC}"
echo "1. Configurar cron job: * * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
echo "2. Verificar permissões de storage e bootstrap/cache"
echo "3. Configurar variáveis de ambiente no .env"
echo "4. Testar a aplicação"
echo ""
echo -e "${GREEN}✨ Deploy concluído!${NC}"

