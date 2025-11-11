@echo off
echo ========================================
echo Preparando projeto para upload
echo ========================================
echo.

echo [1/3] Limpando cache...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo.

echo [2/3] Criando cache de producao...
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo.

echo [3/3] Verificando arquivos...
if exist index.php (
    echo [OK] index.php encontrado
) else (
    echo [ERRO] index.php nao encontrado
)

if exist .htaccess (
    echo [OK] .htaccess encontrado
) else (
    echo [ERRO] .htaccess nao encontrado
)

echo.
echo ========================================
echo Preparacao concluida!
echo ========================================
echo.
echo Proximo passo: Fazer upload de TODOS os arquivos para o servidor
echo.
pause

