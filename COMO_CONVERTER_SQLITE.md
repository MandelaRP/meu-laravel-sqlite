# 🔄 Como Converter SQLite para MySQL - Guia Rápido

## ❌ O Problema

Você tentou importar `database.sqlite` no phpMyAdmin e recebeu:
```
#1064 - Você tem um erro de sintaxe no seu SQL próximo a 'SQLite format 3'
```

**Motivo:** SQLite e MySQL são bancos diferentes e incompatíveis.

---

## ✅ Solução Rápida (3 Passos)

### 1️⃣ Executar Comando de Conversão

No terminal, na raiz do projeto:

```bash
php artisan db:convert-sqlite-to-mysql
```

Isso vai gerar o arquivo: `database/mysql_export.sql`

### 2️⃣ Importar no phpMyAdmin

1. Acesse phpMyAdmin da hospedagem
2. Selecione ou crie o banco de dados
3. Clique em **"Importar"**
4. Escolha o arquivo: `database/mysql_export.sql`
5. Clique em **"Executar"**

### 3️⃣ Configurar .env

No servidor, configure o `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nome_do_seu_banco
DB_USERNAME=usuario_do_banco
DB_PASSWORD=senha_do_banco
```

---

## 🎯 Alternativa: Usar Migrações (RECOMENDADO)

**Melhor prática:** Em vez de converter dados, use as migrações do Laravel:

1. **Criar banco MySQL vazio** no servidor
2. **Configurar .env** com MySQL
3. **Executar:** `php artisan migrate --force`

Isso cria todas as tabelas automaticamente!

---

## 📋 Opções do Comando

```bash
# Especificar arquivo SQLite
php artisan db:convert-sqlite-to-mysql --sqlite-path=database/database.sqlite

# Especificar arquivo de saída
php artisan db:convert-sqlite-to-mysql --output=database/meu_export.sql

# Ambos
php artisan db:convert-sqlite-to-mysql --sqlite-path=database/database.sqlite --output=database/mysql_export.sql
```

---

## ⚠️ Importante

- O arquivo `.sqlite` **NÃO pode** ser importado diretamente no phpMyAdmin
- Você **DEVE** converter primeiro para SQL
- Ou usar as **migrações** do Laravel (melhor opção)

---

## 📚 Documentação Completa

Veja `GUIA_CONVERSAO_SQLITE_MYSQL.md` para mais detalhes e alternativas.

