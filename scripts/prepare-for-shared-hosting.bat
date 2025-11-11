@echo off
REM Script para preparar o projeto Laravel para hospedagem compartilhada (Windows)
REM Uso: scripts\prepare-for-shared-hosting.bat

echo 🚀 Preparando projeto para hospedagem compartilhada...

REM Verificar se está na raiz do projeto
if not exist "artisan" (
    echo ❌ Erro: Execute este script na raiz do projeto Laravel
    exit /b 1
)

echo 📦 Instalando dependências do Composer (produção)...
call composer install --optimize-autoloader --no-dev --no-interaction
if errorlevel 1 (
    echo ❌ Erro ao instalar dependências do Composer
    exit /b 1
)

echo 📦 Instalando dependências do NPM...
call npm install --production
if errorlevel 1 (
    echo ❌ Erro ao instalar dependências do NPM
    exit /b 1
)

echo 🔨 Compilando assets para produção...
call npm run build
if errorlevel 1 (
    echo ❌ Erro ao compilar assets
    exit /b 1
)

echo 🗑️  Limpando arquivos desnecessários...

REM Remover node_modules (não precisa no servidor)
if exist "node_modules" rmdir /s /q node_modules

REM Limpar cache de desenvolvimento
call php artisan cache:clear
call php artisan config:clear
call php artisan route:clear
call php artisan view:clear

REM Limpar logs antigos
for /f "delims=" %%i in ('dir /b /s storage\logs\*.log 2^>nul') do del "%%i"

REM Limpar cache do framework
if exist "storage\framework\cache\*" del /q /f "storage\framework\cache\*"
if exist "storage\framework\sessions\*" del /q /f "storage\framework\sessions\*"
if exist "storage\framework\views\*" del /q /f "storage\framework\views\*"

REM Criar estrutura de pastas
if not exist "storage\framework\cache\data" mkdir "storage\framework\cache\data"
if not exist "storage\framework\sessions" mkdir "storage\framework\sessions"
if not exist "storage\framework\views" mkdir "storage\framework\views"

echo ✅ Preparação concluída!
echo.
echo 📋 Próximos passos:
echo 1. Compacte os arquivos (exceto node_modules, .git, tests)
echo 2. Faça upload para o servidor
echo 3. Configure o arquivo .env no servidor
echo 4. Execute no servidor:
echo    - php artisan key:generate
echo    - php artisan migrate
echo    - php artisan storage:link
echo    - php artisan config:cache
echo    - php artisan route:cache
echo    - php artisan view:cache
echo.
echo ✨ Pronto para deploy!

