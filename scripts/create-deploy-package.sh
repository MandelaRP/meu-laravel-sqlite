#!/bin/bash

# Script para criar pacote de deploy (arquivos para upload)
# Cria um arquivo .zip com todos os arquivos necessários

echo "📦 Criando pacote de deploy..."

# Cores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
PACKAGE_NAME="luckpay-deploy-${TIMESTAMP}.zip"

echo -e "${YELLOW}🗜️  Compactando arquivos...${NC}"

# Criar arquivo zip excluindo arquivos desnecessários
zip -r "$PACKAGE_NAME" . \
    -x "*.git*" \
    -x "*node_modules*" \
    -x "*tests*" \
    -x "*.env" \
    -x "*.log" \
    -x "*storage/logs/*.log" \
    -x "*storage/framework/cache/*" \
    -x "*storage/framework/sessions/*" \
    -x "*storage/framework/views/*" \
    -x "*storage/debugbar/*" \
    -x "*.DS_Store" \
    -x "*Thumbs.db" \
    -x "*.idea*" \
    -x "*.vscode*" \
    -x "*.zip" \
    -x "*.tar.gz" \
    -x "docker/*" \
    -x "docs/*" \
    -x "*.md" \
    -x "!DEPLOY.md" \
    -x "scripts/*" \
    -x "!scripts/deploy-shared-hosting.sh"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Pacote criado: ${PACKAGE_NAME}${NC}"
    echo ""
    echo -e "${YELLOW}📋 Tamanho do pacote:${NC}"
    du -h "$PACKAGE_NAME"
    echo ""
    echo -e "${YELLOW}📤 Próximos passos:${NC}"
    echo "1. Faça upload do arquivo ${PACKAGE_NAME} para o servidor"
    echo "2. Extraia na pasta do projeto"
    echo "3. Execute o script: scripts/deploy-shared-hosting.sh"
else
    echo -e "${RED}❌ Erro ao criar pacote${NC}"
    exit 1
fi

