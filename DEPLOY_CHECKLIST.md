# ✅ Checklist de Deploy - LuckPay

Use este checklist para garantir que nada seja esquecido durante o deploy.

## 📋 Pré-Deploy (Local)

### Preparação
- [ ] Testar aplicação localmente
- [ ] Executar testes: `php artisan test`
- [ ] Verificar se não há erros no console
- [ ] Compilar assets: `npm run build`
- [ ] Verificar se build foi gerado em `public/build/`

### Dependências
- [ ] Instalar dependências PHP: `composer install --optimize-autoloader --no-dev`
- [ ] Instalar dependências Node: `npm install --production`
- [ ] Verificar se pasta `vendor/` existe
- [ ] Verificar se pasta `node_modules/` foi removida (não enviar)

### Configuração
- [ ] Criar arquivo `.env.example` atualizado
- [ ] Documentar todas as variáveis de ambiente necessárias
- [ ] Verificar configurações de banco de dados
- [ ] Verificar configurações de email
- [ ] Verificar configurações de storage

### Limpeza
- [ ] Limpar cache: `php artisan cache:clear`
- [ ] Limpar logs antigos
- [ ] Remover arquivos temporários
- [ ] Verificar tamanho do pacote (idealmente < 50MB)

---

## 🚀 Deploy no Servidor

### Upload
- [ ] Fazer upload de todos os arquivos
- [ ] Verificar se estrutura de pastas está correta
- [ ] Verificar se `vendor/` foi enviado
- [ ] Verificar se `public/build/` foi enviado

### Configuração Inicial
- [ ] Criar arquivo `.env` no servidor
- [ ] Configurar `APP_KEY` (gerar com `php artisan key:generate`)
- [ ] Configurar `APP_URL` (URL completa do site)
- [ ] Configurar `APP_ENV=production`
- [ ] Configurar `APP_DEBUG=false`
- [ ] Configurar credenciais do banco de dados
- [ ] Configurar outras variáveis necessárias

### Banco de Dados
- [ ] Criar banco de dados no servidor
- [ ] Importar dump SQL (se houver)
- [ ] Executar migrações: `php artisan migrate --force`
- [ ] Verificar se tabelas foram criadas

### Permissões
- [ ] Configurar permissões: `chmod -R 755 storage`
- [ ] Configurar permissões: `chmod -R 755 bootstrap/cache`
- [ ] Verificar se servidor web tem permissão de escrita

### Links e Cache
- [ ] Criar link simbólico: `php artisan storage:link`
- [ ] Limpar cache: `php artisan config:clear`
- [ ] Cache de configuração: `php artisan config:cache`
- [ ] Cache de rotas: `php artisan route:cache`
- [ ] Cache de views: `php artisan view:cache`
- [ ] Otimizar: `php artisan optimize`

### Estrutura de Pastas (Hospedagem Compartilhada)
- [ ] Verificar se `public/index.php` aponta para caminho correto
- [ ] Verificar se `.htaccess` está configurado
- [ ] Verificar se link simbólico `storage` funciona
- [ ] Testar acesso a arquivos públicos

---

## ⚙️ Configurações do Servidor

### Cron Job
- [ ] Configurar cron: `* * * * * cd /caminho && php artisan schedule:run`
- [ ] Testar execução do cron
- [ ] Verificar logs do cron

### Queue Worker (se necessário)
- [ ] Configurar supervisor ou processo em background
- [ ] Verificar se filas estão sendo processadas
- [ ] Configurar restart automático

### Web Server
- [ ] Configurar Nginx/Apache
- [ ] Configurar DocumentRoot para `public/`
- [ ] Configurar SSL/HTTPS
- [ ] Testar acesso HTTP/HTTPS

### PHP
- [ ] Verificar versão PHP (8.2+)
- [ ] Verificar extensões PHP necessárias
- [ ] Verificar `upload_max_filesize` e `post_max_size`
- [ ] Verificar `memory_limit`

---

## 🧪 Testes Pós-Deploy

### Funcionalidades Básicas
- [ ] Acessar página inicial
- [ ] Testar login
- [ ] Testar registro (se houver)
- [ ] Testar upload de arquivos
- [ ] Testar salvamento de dados

### Performance
- [ ] Verificar tempo de carregamento
- [ ] Verificar se assets carregam corretamente
- [ ] Verificar se imagens aparecem
- [ ] Testar em diferentes navegadores

### Segurança
- [ ] Verificar se `.env` não é acessível publicamente
- [ ] Verificar se `storage/` não é acessível diretamente
- [ ] Verificar se SSL está funcionando
- [ ] Testar proteção CSRF

### Logs
- [ ] Verificar logs do Laravel: `storage/logs/laravel.log`
- [ ] Verificar logs do servidor web
- [ ] Verificar se não há erros

---

## 🔄 Manutenção Contínua

### Backups
- [ ] Configurar backup do banco de dados
- [ ] Configurar backup dos arquivos
- [ ] Testar restauração de backup

### Monitoramento
- [ ] Configurar monitoramento de uptime
- [ ] Configurar alertas de erro
- [ ] Verificar uso de recursos

### Atualizações
- [ ] Documentar processo de atualização
- [ ] Testar atualizações em ambiente de staging
- [ ] Planejar janela de manutenção

---

## 📞 Suporte

Se algo der errado:
1. Verificar logs: `storage/logs/laravel.log`
2. Verificar permissões de arquivos
3. Verificar configuração do `.env`
4. Verificar logs do servidor web
5. Consultar documentação do Laravel

---

**Última atualização:** $(date)

