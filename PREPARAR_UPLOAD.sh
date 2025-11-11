#!/bin/bash

echo "========================================"
echo "Preparando projeto para upload"
echo "========================================"
echo ""

echo "[1/3] Limpando cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo ""

echo "[2/3] Criando cache de produção..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo ""

echo "[3/3] Verificando arquivos..."
if [ -f "index.php" ]; then
    echo "[OK] index.php encontrado"
else
    echo "[ERRO] index.php não encontrado"
fi

if [ -f ".htaccess" ]; then
    echo "[OK] .htaccess encontrado"
else
    echo "[ERRO] .htaccess não encontrado"
fi

echo ""
echo "========================================"
echo "Preparação concluída!"
echo "========================================"
echo ""
echo "Próximo passo: Fazer upload de TODOS os arquivos para o servidor"
echo ""

