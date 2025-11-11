# 🔄 Guia: Converter SQLite para MySQL

## ❌ O Problema

Você tentou importar um arquivo `.sqlite` no phpMyAdmin e recebeu o erro:

```
#1064 - Você tem um erro de sintaxe no seu SQL próximo a 'SQLite format 3' na linha 1
```

### Por quê isso acontece?

- **SQLite** e **MySQL** são sistemas de banco de dados **diferentes**
- SQLite usa formato **binário** (começa com "SQLite format 3")
- MySQL espera arquivos **SQL** (texto puro)
- **Não são compatíveis diretamente**

---

## ✅ Solução: Converter SQLite para SQL (MySQL)

### Opção 1: Usar Comando Artisan (RECOMENDADO) ⭐

Criei um comando Laravel que faz a conversão automaticamente:

#### Passo 1: Executar o comando

```bash
php artisan db:convert-sqlite-to-mysql
```

**Opções disponíveis:**
```bash
# Especificar arquivo SQLite de origem
php artisan db:convert-sqlite-to-mysql --sqlite-path=database/database.sqlite

# Especificar arquivo de saída
php artisan db:convert-sqlite-to-mysql --output=database/mysql_export.sql

# Ambos
php artisan db:convert-sqlite-to-mysql --sqlite-path=database/database.sqlite --output=database/mysql_export.sql
```

#### Passo 2: Importar no phpMyAdmin

1. Acesse o phpMyAdmin da sua hospedagem
2. Selecione ou crie o banco de dados
3. Clique em **"Importar"** (aba superior)
4. Clique em **"Escolher arquivo"**
5. Selecione o arquivo gerado: `database/mysql_export.sql`
6. Clique em **"Executar"**

#### Passo 3: Configurar .env

Após importar, configure o `.env` no servidor:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario_banco
DB_PASSWORD=senha_banco
```

---

### Opção 2: Usar Ferramenta Online

Se não conseguir executar o comando, use ferramentas online:

1. **SQLite to MySQL Converter**
   - https://www.rebasedata.com/convert-sqlite-to-mysql-online
   - Upload do arquivo `.sqlite`
   - Download do arquivo `.sql`

2. **DB Browser for SQLite**
   - Baixe: https://sqlitebrowser.org/
   - Abra o arquivo `.sqlite`
   - Export → Export Database to SQL
   - Ajuste manualmente a sintaxe para MySQL

---

### Opção 3: Usar Migrações do Laravel (MELHOR PRÁTICA) ⭐⭐

**Esta é a melhor opção!** Em vez de converter dados, use as migrações:

#### Passo 1: No servidor, criar banco MySQL vazio

```sql
CREATE DATABASE luckpay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Passo 2: Configurar .env

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=luckpay
DB_USERNAME=usuario
DB_PASSWORD=senha
```

#### Passo 3: Executar migrações

```bash
php artisan migrate --force
```

#### Passo 4: (Opcional) Importar dados

Se você precisa dos dados do SQLite:

1. Use o comando de conversão para gerar SQL
2. Ou use seeders do Laravel
3. Ou importe manualmente via phpMyAdmin

---

## 🔧 Solução Rápida: Script Manual

Se o comando Artisan não funcionar, use este script PHP:

```php
<?php
// converter.php
$sqlite = new PDO('sqlite:database/database.sqlite');
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$output = fopen('mysql_export.sql', 'w');

// Obter tabelas
$tables = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    // Estrutura
    $create = $sqlite->query("SELECT sql FROM sqlite_master WHERE name='{$table}'")->fetchColumn();
    fwrite($output, "DROP TABLE IF EXISTS `{$table}`;\n");
    fwrite($output, str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $create) . ";\n\n");
    
    // Dados
    $rows = $sqlite->query("SELECT * FROM {$table}")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
        $columns = array_keys($rows[0]);
        $values = [];
        foreach ($rows as $row) {
            $vals = array_map(fn($v) => $v === null ? 'NULL' : "'" . addslashes($v) . "'", $row);
            $values[] = "(" . implode(',', $vals) . ")";
        }
        fwrite($output, "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES\n");
        fwrite($output, implode(",\n", $values) . ";\n\n");
    }
}

fclose($output);
echo "Conversão concluída! Arquivo: mysql_export.sql\n";
```

Execute:
```bash
php converter.php
```

---

## 📋 Checklist de Conversão

- [ ] Executar comando: `php artisan db:convert-sqlite-to-mysql`
- [ ] Verificar se arquivo SQL foi gerado
- [ ] Criar banco MySQL no servidor (se não existir)
- [ ] Importar arquivo SQL no phpMyAdmin
- [ ] Verificar se dados foram importados corretamente
- [ ] Configurar `.env` com credenciais MySQL
- [ ] Testar conexão: `php artisan migrate:status`
- [ ] Verificar se aplicação funciona

---

## ⚠️ Problemas Comuns

### Erro: "Table doesn't exist"
- Verifique se o banco foi criado
- Verifique se o nome do banco está correto no `.env`

### Erro: "Access denied"
- Verifique usuário e senha no `.env`
- Verifique permissões do usuário MySQL

### Dados não aparecem
- Verifique se a importação foi bem-sucedida
- Verifique logs do phpMyAdmin
- Tente importar novamente

### Caracteres especiais quebrados
- Certifique-se de usar `utf8mb4` no banco
- Verifique charset no `.env`

---

## 🎯 Recomendação Final

**Para produção, use migrações!**

1. ✅ Crie banco MySQL vazio
2. ✅ Configure `.env`
3. ✅ Execute `php artisan migrate`
4. ✅ (Opcional) Importe dados via seeder ou conversão

Isso garante que a estrutura está sempre atualizada e compatível.

---

## 📞 Precisa de Ajuda?

Se ainda tiver problemas:
1. Verifique logs: `storage/logs/laravel.log`
2. Teste conexão: `php artisan db:show`
3. Verifique permissões do banco
4. Consulte documentação do Laravel: https://laravel.com/docs/database

