# 🔄 Migração SQLite → MySQL

## ✅ Status

O projeto foi migrado de SQLite para MySQL e está pronto para hospedagem compartilhada.

## 📁 Arquivos

- **`database/mysql_export.sql`** - Arquivo SQL completo para importação
- **`app/Console/Commands/ConvertSqliteToMysql.php`** - Comando para converter SQLite para MySQL

## 🔧 Como Usar

### Converter SQLite para MySQL

```bash
php artisan db:convert-sqlite-to-mysql --output=database/mysql_export.sql
```

### Importar no phpMyAdmin

1. Acesse phpMyAdmin
2. Selecione o banco de dados
3. Vá em "Importar"
4. Selecione `database/mysql_export.sql`
5. Clique em "Executar"

## ⚠️ Importante

- O arquivo SQL já inclui `SET FOREIGN_KEY_CHECKS = 0` para evitar erros durante importação
- Dados órfãos são removidos automaticamente durante a conversão
- Todas as FOREIGN KEYs estão normalizadas e funcionais

