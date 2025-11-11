<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertSqliteToMysql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:convert-sqlite-to-mysql 
                            {--output= : Caminho do arquivo SQL de saída (padrão: database/mysql_export.sql)}
                            {--sqlite-path= : Caminho do arquivo SQLite (padrão: database/database.sqlite)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converte banco de dados SQLite para formato SQL compatível com MySQL';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sqlitePath = $this->option('sqlite-path') ?: database_path('database.sqlite');
        $outputPath = $this->option('output') ?: database_path('mysql_export.sql');

        // Verificar se arquivo SQLite existe
        if (!file_exists($sqlitePath)) {
            $this->error("Arquivo SQLite não encontrado: {$sqlitePath}");
            return Command::FAILURE;
        }

        $this->info("🔄 Convertendo SQLite para MySQL...");
        $this->info("📁 SQLite: {$sqlitePath}");
        $this->info("📁 Saída: {$outputPath}");

        // Conectar ao SQLite
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => $sqlitePath]);

        try {
            // Obter todas as tabelas
            $tables = $this->getTables();
            
            if (empty($tables)) {
                $this->warn("⚠️  Nenhuma tabela encontrada no banco SQLite.");
                return Command::FAILURE;
            }

            $this->info("📊 Encontradas " . count($tables) . " tabelas.");

            // Ordenar tabelas por dependências (FOREIGN KEYs)
            $tables = $this->orderTablesByDependencies($tables);

            // Abrir arquivo para escrita
            $sqlFile = fopen($outputPath, 'w');
            
            if (!$sqlFile) {
                $this->error("❌ Não foi possível criar o arquivo de saída: {$outputPath}");
                return Command::FAILURE;
            }

            // Escrever cabeçalho
            fwrite($sqlFile, "-- Exportação de SQLite para MySQL\n");
            fwrite($sqlFile, "-- Gerado em: " . date('Y-m-d H:i:s') . "\n");
            fwrite($sqlFile, "-- \n\n");
            fwrite($sqlFile, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($sqlFile, "SET time_zone = \"+00:00\";\n\n");
            fwrite($sqlFile, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
            fwrite($sqlFile, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
            fwrite($sqlFile, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
            fwrite($sqlFile, "/*!40101 SET NAMES utf8mb4 */;\n\n");

            // Processar cada tabela
            foreach ($tables as $table) {
                $this->line("📋 Processando tabela: {$table}");
                $this->exportTable($sqlFile, $table);
            }

            // Escrever rodapé
            fwrite($sqlFile, "\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
            fwrite($sqlFile, "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n");
            fwrite($sqlFile, "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n");

            fclose($sqlFile);

            $this->info("✅ Conversão concluída!");
            $this->info("📄 Arquivo gerado: {$outputPath}");
            $this->info("\n📋 Próximos passos:");
            $this->info("1. Acesse o phpMyAdmin da sua hospedagem");
            $this->info("2. Selecione ou crie o banco de dados");
            $this->info("3. Vá em 'Importar'");
            $this->info("4. Selecione o arquivo: {$outputPath}");
            $this->info("5. Clique em 'Executar'");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Erro durante a conversão: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Obter lista de tabelas do SQLite
     */
    private function getTables(): array
    {
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        return array_map(fn($table) => $table->name, $tables);
    }

    /**
     * Ordenar tabelas por dependências (FOREIGN KEYs)
     */
    private function orderTablesByDependencies(array $tables): array
    {
        // Ordem de prioridade baseada em dependências conhecidas
        $priority = [
            // Tabelas sem dependências (primeiro)
            'migrations' => 1,
            'password_reset_tokens' => 1,
            'sessions' => 1,
            'cache' => 1,
            'cache_locks' => 1,
            'jobs' => 1,
            'job_batches' => 1,
            'failed_jobs' => 1,
            'roles' => 2,
            'permissions' => 2,
            'acquirers' => 2,
            'system_settings' => 2,
            
            // users deve ser criada antes de todas que a referenciam
            'users' => 3,
            
            // Tabelas que dependem de users ou outras tabelas básicas
            'categories' => 4, // categories depende de users
            'groups' => 4,
            'members' => 4,
            'addresses' => 4,
            'financial_settings' => 4,
            'order_bumps' => 4,
            'liberpay_sales' => 4,
            'fullpix_sales' => 4,
            'pix_keys' => 4,
            'withdrawals' => 4,
            'system_images' => 4,
            'products' => 5, // products depende de categories e users
            'checkouts' => 5, // checkouts depende de products
            'transactions' => 5, // transactions depende de products e checkouts
            
            // Tabelas de relacionamento (dependem de roles, permissions, members, users)
            'permission_role' => 5,
            'role_user' => 5,
            'permission_user' => 5,
            'member_role' => 5,
        ];

        // Ordenar tabelas pela prioridade
        usort($tables, function($a, $b) use ($priority) {
            $priorityA = $priority[$a] ?? 99;
            $priorityB = $priority[$b] ?? 99;
            
            if ($priorityA === $priorityB) {
                return strcmp($a, $b);
            }
            
            return $priorityA <=> $priorityB;
        });

        return $tables;
    }

    /**
     * Exportar tabela para SQL
     */
    private function exportTable($file, string $tableName): void
    {
        // Obter estrutura da tabela
        $createTable = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$tableName]);
        
        if (empty($createTable)) {
            return;
        }

        $createSql = $createTable[0]->sql;
        
        // Converter sintaxe SQLite para MySQL
        $createSql = $this->convertCreateTable($createSql, $tableName);
        
        fwrite($file, "\n-- Estrutura da tabela `{$tableName}`\n");
        fwrite($file, "DROP TABLE IF EXISTS `{$tableName}`;\n");
        fwrite($file, $createSql . ";\n\n");

        // Obter dados
        $rows = DB::table($tableName)->get();
        
        if ($rows->isEmpty()) {
            fwrite($file, "-- Dados da tabela `{$tableName}`: vazia\n\n");
            return;
        }

        fwrite($file, "-- Dados da tabela `{$tableName}`\n");
        fwrite($file, "LOCK TABLES `{$tableName}` WRITE;\n");
        fwrite($file, "/*!40000 ALTER TABLE `{$tableName}` DISABLE KEYS */;\n");

        // Inserir dados
        $columns = array_keys((array) $rows->first());
        $insertSql = "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES\n";

        $values = [];
        $batchSize = 100; // Inserir em lotes de 100 registros
        $count = 0;
        
        foreach ($rows as $row) {
            $rowArray = (array) $row;
            $rowValues = [];
            
            foreach ($columns as $col) {
                $value = $rowArray[$col] ?? null;
                
                if ($value === null) {
                    $rowValues[] = 'NULL';
                } elseif (is_bool($value)) {
                    $rowValues[] = $value ? '1' : '0';
                } elseif (is_numeric($value) && !is_string($value)) {
                    $rowValues[] = $value;
                } else {
                    // Escapar strings corretamente para MySQL
                    $escaped = str_replace(
                        ['\\', "\x00", "\n", "\r", "'", '"', "\x1a"],
                        ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
                        (string) $value
                    );
                    $rowValues[] = "'{$escaped}'";
                }
            }
            
            $values[] = "(" . implode(', ', $rowValues) . ")";
            $count++;
            
            // Escrever em lotes para evitar arquivos muito grandes
            if ($count % $batchSize === 0) {
                fwrite($file, $insertSql . implode(",\n", $values) . ";\n");
                $values = [];
                $insertSql = "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES\n";
            }
        }
        
        // Escrever registros restantes
        if (!empty($values)) {
            fwrite($file, $insertSql . implode(",\n", $values) . ";\n");
        }
        fwrite($file, "/*!40000 ALTER TABLE `{$tableName}` ENABLE KEYS */;\n");
        fwrite($file, "UNLOCK TABLES;\n\n");
    }

    /**
     * Converter CREATE TABLE do SQLite para MySQL
     */
    private function convertCreateTable(string $sql, string $tableName): string
    {
        // Remover comentários e normalizar
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        $sql = trim($sql);
        
        // Extrair definições de colunas
        if (preg_match('/CREATE TABLE\s+(?:["\']?\w+["\']?\s+)?\((.+)\)/is', $sql, $matches)) {
            $columnsDef = $matches[1];
            
            // Converter cada definição de coluna
            $columns = preg_split('/,\s*(?![^()]*\))/', $columnsDef);
            $convertedColumns = [];
            $primaryKeys = [];
            $checkConstraints = [];
            
            foreach ($columns as $column) {
                $column = trim($column);
                if (empty($column)) continue;
                
                // Primeiro, tratar FOREIGN KEY antes de processar colunas
                if (preg_match('/\bforeign\s+key\b/i', $column)) {
                    // FOREIGN KEY - converter para sintaxe MySQL
                    $column = preg_replace('/["\'](\w+)["\']/', '`$1`', $column);
                    // Normalizar FOREIGN KEY para maiúsculas
                    $column = preg_replace('/\bforeign\s+key\b/i', 'FOREIGN KEY', $column);
                    // Corrigir sintaxe: references deve ser REFERENCES
                    $column = preg_replace('/\breferences\b/i', 'REFERENCES', $column);
                    // Garantir que a sintaxe está correta: FOREIGN KEY(`col`) REFERENCES `table`(`col`)
                    $column = preg_replace_callback('/FOREIGN\s+KEY\s*\(`?(\w+)`?\)\s+references\s+`?(\w+)`?\s*\(`?(\w+)`?\)/i', function($m) {
                        return "FOREIGN KEY (`{$m[1]}`) REFERENCES `{$m[2]}` (`{$m[3]}`)";
                    }, $column);
                    $convertedColumns[] = $column;
                    continue;
                }
                
                // Primeiro, tratar PRIMARY KEY antes de processar colunas
                if (preg_match('/\bprimary\s+key\s*\(/i', $column)) {
                    // PRIMARY KEY (em qualquer case) - normalizar
                    $column = preg_replace_callback('/\bprimary\s+key\s*\(([^)]+)\)/i', function($m) {
                        $cols = preg_split('/\s*,\s*/', trim($m[1], ' "\''));
                        $colsFormatted = array_map(function($col) {
                            $col = trim($col, ' "\'');
                            return "`{$col}`";
                        }, $cols);
                        return "PRIMARY KEY (" . implode(', ', $colsFormatted) . ")";
                    }, $column);
                    $convertedColumns[] = $column;
                    continue;
                }
                
                // Remover aspas duplas e simples dos nomes
                $column = preg_replace('/["\'](\w+)["\']/', '$1', $column);
                
                // Extrair nome da coluna e definição
                if (preg_match('/^(\w+)\s+(.+)$/i', $column, $colMatches)) {
                    $colName = trim($colMatches[1]);
                    $colDef = trim($colMatches[2]);
                    
                    // Converter tipos de dados
                    $colDef = preg_replace('/\bINTEGER\b/i', 'INT', $colDef);
                    
                    // Se for VARCHAR sem tamanho, adicionar tamanho padrão
                    if (preg_match('/\bvarchar\b/i', $colDef) && !preg_match('/varchar\s*\(/i', $colDef)) {
                        $colDef = preg_replace('/\bvarchar\b/i', 'VARCHAR(255)', $colDef);
                    }
                    
                    // Remover CHECK constraints da definição da coluna (serão adicionadas como constraints separadas)
                    // No MySQL, CHECK deve vir depois de todas as colunas, não no meio
                    if (preg_match('/CHECK\s*\(/i', $colDef)) {
                        // Extrair a constraint CHECK completa (pode ter parênteses aninhados)
                        if (preg_match('/CHECK\s*\(([^)]*(?:\([^)]*\)[^)]*)*)\)/i', $colDef, $checkMatch)) {
                            $checkConstraint = trim($checkMatch[0]);
                            // Remover CHECK da definição da coluna (incluindo qualquer espaço antes)
                            $colDef = preg_replace('/\s*CHECK\s*\([^)]*(?:\([^)]*\)[^)]*)*\)/i', '', $colDef);
                            // Limpar parênteses extras que possam ter ficado
                            $colDef = preg_replace('/\)\s*\)/', ')', $colDef);
                            $colDef = preg_replace('/\(\s*\(/', '(', $colDef);
                            // Adicionar à lista de constraints CHECK (será processada depois)
                            $checkConstraints[] = $checkConstraint;
                        }
                    }
                    
                    $colDef = preg_replace('/\bTEXT\b/i', 'TEXT', $colDef);
                    $colDef = preg_replace('/\bBLOB\b/i', 'BLOB', $colDef);
                    $colDef = preg_replace('/\bREAL\b/i', 'DOUBLE', $colDef);
                    $colDef = preg_replace('/\bNUMERIC\b/i', 'DECIMAL(10,2)', $colDef);
                    
                    // Converter AUTOINCREMENT
                    $colDef = preg_replace('/\bAUTOINCREMENT\b/i', 'AUTO_INCREMENT', $colDef);
                    
                    // Normalizar NOT NULL
                    $colDef = preg_replace('/\bnot null\b/i', 'NOT NULL', $colDef);
                    $colDef = preg_replace('/\bnull\b/i', 'NULL', $colDef);
                    
                    // Remover PRIMARY KEY da definição da coluna (será adicionado como constraint separada)
                    if (preg_match('/\bprimary\s+key\b/i', $colDef)) {
                        $colDef = preg_replace('/\s*\bprimary\s+key\b/i', '', $colDef);
                        $primaryKeys[] = $colName;
                    }
                    
                    // Converter DEFAULT - tratar todos os casos
                    // Processar DEFAULT usando regex mais simples e direto
                    // Primeiro, tratar valores com parênteses e aspas aninhadas
                    // Usar regex que captura parênteses aninhados corretamente
                    $colDef = preg_replace_callback('/DEFAULT\s+\(([^)]*(?:\([^)]*\)[^)]*)*)\)/i', function($m) {
                        $value = trim($m[1]);
                        // Remover todas as aspas (simples e duplas) do início e fim
                        $value = preg_replace('/^[\'"]+|[\'"]+$/', '', $value);
                        // Remover qualquer aspas dupla restante (ex: '' no final ou início)
                        $value = preg_replace("/^''|''$/", '', $value);
                        // Limpar qualquer aspas duplas no meio também (mas preservar o conteúdo)
                        // Se o valor contém parênteses, pode ter aspas duplas que precisam ser removidas
                        $value = preg_replace("/''/", '', $value);
                        if (is_numeric($value)) {
                            return 'DEFAULT ' . $value;
                        }
                        return "DEFAULT '{$value}'";
                    }, $colDef);
                    
                    // CURRENT_TIMESTAMP - remover aspas
                    $colDef = preg_replace("/DEFAULT\s+['\"]?CURRENT_TIMESTAMP['\"]?/i", 'DEFAULT CURRENT_TIMESTAMP', $colDef);
                    
                    // Valores numéricos diretos sem parênteses
                    $colDef = preg_replace_callback('/DEFAULT\s+(\d+(?:\.\d+)?)/i', function($m) {
                        return 'DEFAULT ' . $m[1];
                    }, $colDef);
                    
                    // Normalizar DEFAULT em minúsculas para maiúsculas e adicionar aspas se necessário
                    $colDef = preg_replace_callback('/\bdefault\s+([\'"]?)([^\'"]+)\1/i', function($m) {
                        $value = $m[2];
                        // Se não é CURRENT_TIMESTAMP, adicionar aspas
                        if (strtoupper($value) !== 'CURRENT_TIMESTAMP') {
                            return "DEFAULT '{$value}'";
                        }
                        return 'DEFAULT CURRENT_TIMESTAMP';
                    }, $colDef);
                    
                    // Garantir que DEFAULT está em maiúsculas
                    $colDef = preg_replace('/\bdefault\b/i', 'DEFAULT', $colDef);
                    
                    // Converter datetime para TIMESTAMP se tiver DEFAULT CURRENT_TIMESTAMP
                    // Fazer isso DEPOIS de processar DEFAULT
                    if (preg_match('/\bdatetime\b/i', $colDef) && preg_match('/DEFAULT\s+CURRENT_TIMESTAMP/i', $colDef)) {
                        $colDef = preg_replace('/\bdatetime\b/i', 'TIMESTAMP', $colDef);
                    }
                    
                    $convertedColumns[] = "`{$colName}` {$colDef}";
                } elseif (preg_match('/PRIMARY KEY\s*\(([^)]+)\)/i', $column, $pkMatches)) {
                    // PRIMARY KEY como constraint separada (já em maiúsculas)
                    $pkCols = preg_split('/\s*,\s*/', trim($pkMatches[1], ' "\''));
                    $pkColsFormatted = [];
                    foreach ($pkCols as $pkCol) {
                        $pkCol = trim($pkCol, ' "\'');
                        $primaryKeys[] = $pkCol;
                        $pkColsFormatted[] = "`{$pkCol}`";
                    }
                    $convertedColumns[] = "PRIMARY KEY (" . implode(', ', $pkColsFormatted) . ")";
                } elseif (preg_match('/FOREIGN KEY/i', $column)) {
                    // FOREIGN KEY - converter para sintaxe MySQL (caso não tenha sido capturado antes)
                    $column = preg_replace('/["\'](\w+)["\']/', '`$1`', $column);
                    // Normalizar FOREIGN KEY para maiúsculas
                    $column = preg_replace('/\bforeign\s+key\b/i', 'FOREIGN KEY', $column);
                    // Corrigir sintaxe: references deve ser REFERENCES
                    $column = preg_replace('/\breferences\b/i', 'REFERENCES', $column);
                    // Garantir que a sintaxe está correta: FOREIGN KEY(`col`) REFERENCES `table`(`col`)
                    $column = preg_replace_callback('/FOREIGN\s+KEY\s*\(`?(\w+)`?\)\s+references\s+`?(\w+)`?\s*\(`?(\w+)`?\)/i', function($m) {
                        return "FOREIGN KEY (`{$m[1]}`) REFERENCES `{$m[2]}` (`{$m[3]}`)";
                    }, $column);
                    $convertedColumns[] = $column;
                }
            }
            
            // Se há PRIMARY KEY mas não está nas colunas, adicionar
            if (!empty($primaryKeys) && !preg_match('/PRIMARY KEY/i', implode(' ', $convertedColumns))) {
                $pkCols = array_map(fn($col) => "`{$col}`", $primaryKeys);
                $convertedColumns[] = "PRIMARY KEY (" . implode(', ', $pkCols) . ")";
            }
            
            // Adicionar CHECK constraints no final (depois de todas as colunas, antes do PRIMARY KEY)
            if (!empty($checkConstraints)) {
                foreach ($checkConstraints as $check) {
                    // Corrigir sintaxe CHECK - adicionar aspas nos valores se necessário
                    // Primeiro, garantir que tem parêntese de fechamento
                    if (!preg_match('/\)\s*$/', $check)) {
                        $check = rtrim($check) . ')';
                    }
                    // Corrigir sintaxe CHECK - adicionar aspas nos valores se necessário
                    $check = preg_replace_callback('/CHECK\s*\((\w+)\s+IN\s*\(([^)]+)\)\)/i', function($m) {
                        $columnName = $m[1];
                        $values = $m[2];
                        // Dividir valores e adicionar aspas
                        $valueList = preg_split('/\s*,\s*/', $values);
                        $quotedValues = array_map(function($val) {
                            $val = trim($val);
                            // Se já tem aspas, manter; senão, adicionar
                            if (preg_match('/^[\'"]/', $val)) {
                                return $val;
                            }
                            return "'{$val}'";
                        }, $valueList);
                        return "CHECK (`{$columnName}` IN (" . implode(', ', $quotedValues) . "))";
                    }, $check);
                    // Se ainda não foi processado, tentar processar manualmente
                    if (!preg_match('/CHECK\s*\(`\w+`\s+IN\s*\(/', $check)) {
                        // Tentar extrair manualmente
                        if (preg_match('/CHECK\s*\((\w+)\s+in\s*\(([^)]+)\)/i', $check, $m)) {
                            $columnName = $m[1];
                            $values = $m[2];
                            $valueList = preg_split('/\s*,\s*/', $values);
                            $quotedValues = array_map(function($val) {
                                $val = trim($val);
                                if (preg_match('/^[\'"]/', $val)) {
                                    return $val;
                                }
                                return "'{$val}'";
                            }, $valueList);
                            $check = "CHECK (`{$columnName}` IN (" . implode(', ', $quotedValues) . "))";
                        }
                    }
                    // Normalizar CHECK para maiúsculas
                    $check = preg_replace('/\bcheck\b/i', 'CHECK', $check);
                    // Adicionar antes do PRIMARY KEY se houver
                    $hasPrimaryKey = false;
                    foreach ($convertedColumns as $idx => $col) {
                        if (preg_match('/PRIMARY KEY/i', $col)) {
                            array_splice($convertedColumns, $idx, 0, [$check]);
                            $hasPrimaryKey = true;
                            break;
                        }
                    }
                    if (!$hasPrimaryKey) {
                        $convertedColumns[] = $check;
                    }
                }
            }
            
            // Reconstruir CREATE TABLE
            $sql = "CREATE TABLE `{$tableName}` (\n  " . implode(",\n  ", $convertedColumns) . "\n)";
        } else {
            // Fallback: conversão simples
            $sql = preg_replace('/CREATE TABLE\s+["\']?(\w+)["\']?/i', 'CREATE TABLE `$1`', $sql);
            $sql = preg_replace('/["\'](\w+)["\']/i', '`$1`', $sql);
            $sql = preg_replace('/\bINTEGER\b/i', 'INT', $sql);
            $sql = preg_replace('/\bvarchar\b/i', 'VARCHAR(255)', $sql);
            $sql = preg_replace('/\bTEXT\b/i', 'TEXT', $sql);
            $sql = preg_replace('/\bBLOB\b/i', 'BLOB', $sql);
            $sql = preg_replace('/\bREAL\b/i', 'DOUBLE', $sql);
            $sql = preg_replace('/\bNUMERIC\b/i', 'DECIMAL(10,2)', $sql);
            $sql = preg_replace('/\bAUTOINCREMENT\b/i', 'AUTO_INCREMENT', $sql);
        }
        
        // Remover aspas duplas restantes e substituir por backticks
        $sql = preg_replace('/"(\w+)"/', '`$1`', $sql);
        
        // Adicionar ENGINE e CHARSET se não existir
        if (!preg_match('/ENGINE=/i', $sql)) {
            $sql = rtrim($sql, ';');
            $sql .= " ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        }
        
        return $sql;
    }
}

