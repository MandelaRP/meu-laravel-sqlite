# 🚀 Guia de Deploy - LuckPay

## ⚠️ Resposta Direta

**Pergunta:** É possível hospedar apenas subindo arquivos e banco SQL?

**Resposta:** ❌ **NÃO, não é possível apenas subir arquivos e banco SQL diretamente.**

### Por quê?

Laravel (framework usado neste projeto) requer:

1. **Instalação de dependências PHP** via Composer
2. **Compilação de assets** (JavaScript/TypeScript) via Node.js/NPM
3. **Execução de comandos** (`php artisan`) para configuração
4. **Permissões específicas** em pastas `storage/` e `bootstrap/cache/`
5. **Link simbólico** para storage público
6. **Configuração de cron jobs** para tarefas agendadas
7. **Configuração de queue workers** (se usar filas)

---

## ✅ O que Foi Preparado

Criei scripts e guias para facilitar o deploy mesmo em hospedagem compartilhada:

### 📁 Arquivos Criados

1. **`RESUMO_DEPLOY.md`** - Resumo executivo rápido
2. **`GUIA_DEPLOY_COMPLETO.md`** - Guia detalhado completo
3. **`DEPLOY.md`** - Guia geral de deploy
4. **`DEPLOY_CHECKLIST.md`** - Checklist passo a passo
5. **`scripts/prepare-for-shared-hosting.sh`** - Script Linux/Mac
6. **`scripts/prepare-for-shared-hosting.bat`** - Script Windows
7. **`scripts/deploy-shared-hosting.sh`** - Script para servidor
8. **`scripts/create-deploy-package.sh`** - Criar pacote ZIP
9. **`public/index-shared-hosting.php`** - Index.php adaptado
10. **`.htaccess-shared-hosting`** - .htaccess para compartilhada

---

## 🎯 Recomendações de Hospedagem

### 🥇 1ª Opção: Laravel Forge (MAIS RECOMENDADO)

**Custo:** ~R$ 90/mês (Forge $12 + Servidor $6)

**Por quê?**
- ✅ Deploy automático via Git
- ✅ SSL automático (Let's Encrypt)
- ✅ Tudo configurado automaticamente
- ✅ Queue workers e cron jobs configurados
- ✅ Backups automáticos
- ✅ Interface web simples
- ✅ Suporte excelente

**Ideal para:** Quem quer facilidade e não tem muito conhecimento técnico

**Como começar:**
1. Acesse: https://forge.laravel.com
2. Crie conta e conecte GitHub/GitLab
3. Provisione servidor (DigitalOcean, AWS, etc.)
4. Conecte repositório
5. Configure variáveis de ambiente
6. Deploy automático!

---

### 🥈 2ª Opção: VPS Manual

**Custo:** ~R$ 30-60/mês

**Provedores:**
- DigitalOcean ($6/mês) - https://www.digitalocean.com
- Vultr ($6/mês) - https://www.vultr.com
- Linode ($5/mês) - https://www.linode.com
- Contabo (€4.99/mês) - https://contabo.com

**Vantagens:**
- ✅ Mais barato que Forge
- ✅ Controle total
- ✅ Recursos dedicados

**Desvantagens:**
- ❌ Requer conhecimento técnico
- ❌ Configuração manual
- ❌ Manutenção manual

**Ideal para:** Quem tem conhecimento técnico e quer economizar

---

### 🥉 3ª Opção: Hospedagem Compartilhada

**Custo:** ~R$ 10-30/mês

**Requisitos:**
- ✅ Acesso SSH (obrigatório!)
- ✅ Composer instalado
- ✅ Node.js 18+ e NPM
- ✅ PHP 8.2+

**Processo:**
1. Executar `scripts/prepare-for-shared-hosting.sh` (ou .bat no Windows)
2. Fazer upload dos arquivos
3. Executar `scripts/deploy-shared-hosting.sh` no servidor
4. Configurar cron job
5. Configurar estrutura de pastas

**Dificuldade:** ⭐⭐⭐⭐ (Alta)

**Ideal para:** Orçamento muito limitado e acesso SSH disponível

---

## 📋 Processo Rápido

### Para Hospedagem Compartilhada:

**1. Preparação Local (Windows):**
```cmd
scripts\prepare-for-shared-hosting.bat
```

**2. Preparação Local (Linux/Mac):**
```bash
chmod +x scripts/prepare-for-shared-hosting.sh
./scripts/prepare-for-shared-hosting.sh
```

**3. Criar Pacote:**
```bash
chmod +x scripts/create-deploy-package.sh
./scripts/create-deploy-package.sh
```

**4. Upload e Configuração no Servidor:**
- Fazer upload do ZIP
- Extrair no servidor
- Executar: `scripts/deploy-shared-hosting.sh`

---

## 🔧 Configurações Necessárias

### No Servidor:

1. **Arquivo .env:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com.br
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=nome_banco
DB_USERNAME=usuario
DB_PASSWORD=senha
```

2. **Cron Job:**
```
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

3. **Permissões:**
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

4. **Link Simbólico:**
```bash
php artisan storage:link
```

---

## 📊 Comparação Final

| Aspecto | Compartilhada | VPS Manual | Laravel Forge |
|---------|---------------|------------|---------------|
| **Custo** | R$ 10-30 | R$ 30-60 | R$ 90+ |
| **Dificuldade** | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐ |
| **Tempo Setup** | 2-4 horas | 1-2 horas | 15-30 min |
| **Manutenção** | Manual | Manual | Automática |
| **Deploy** | Manual | Manual | Automático |
| **SSL** | Manual | Manual | Automático |
| **Backups** | Manual | Manual | Automático |

---

## 🎯 Minha Recomendação

### Se você:
- **Tem orçamento limitado** → VPS Manual (R$ 30-60/mês)
- **Quer facilidade** → Laravel Forge (R$ 90/mês)
- **Tem acesso SSH em compartilhada** → Pode tentar, mas é trabalhoso

### Para a maioria dos casos:
**Laravel Forge + DigitalOcean** é a melhor opção pelo custo-benefício e facilidade.

---

## 📚 Documentação Completa

- **Guia Completo:** `GUIA_DEPLOY_COMPLETO.md`
- **Checklist:** `DEPLOY_CHECKLIST.md`
- **Resumo:** `RESUMO_DEPLOY.md`

---

## 🆘 Precisa de Ajuda?

1. Leia `GUIA_DEPLOY_COMPLETO.md` primeiro
2. Use o `DEPLOY_CHECKLIST.md` para não esquecer nada
3. Verifique logs: `storage/logs/laravel.log`
4. Consulte documentação do Laravel: https://laravel.com/docs/deployment

---

**Boa sorte com o deploy! 🚀**

