<?php

declare(strict_types = 1);

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use ZipArchive;

class CleanSampleDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:clean-samples {--force : Force cleanup without confirmation} {--mark-only : Only mark samples, do not delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark and delete sample/fake data from the system';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->option('force') && !$this->option('mark-only')) {
            if (!$this->confirm('Tem certeza que deseja marcar e deletar dados fictícios? Esta ação não pode ser desfeita.')) {
                $this->info('Operação cancelada.');
                return Command::SUCCESS;
            }
        }

        try {
            // Criar backup antes de deletar
            $this->createBackup();

            DB::beginTransaction();

            // Marcar dados fictícios
            $this->markSampleData();

            if (!$this->option('mark-only')) {
                // Deletar dados marcados
                $this->deleteSampleData();
            }

            DB::commit();

            $this->info('✓ Limpeza de dados fictícios concluída com sucesso!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Erro ao limpar dados fictícios: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Criar backup do banco e uploads
     */
    private function createBackup(): void
    {
        $this->info('Criando backup...');

        $timestamp = now()->format('Ymd_His');
        $backupDir = storage_path('app/backups');
        
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // Backup do banco SQLite
        $dbPath = database_path('database.sqlite');
        if (File::exists($dbPath)) {
            $backupDbPath = $backupDir . "/db_backup_{$timestamp}.sqlite";
            File::copy($dbPath, $backupDbPath);
            $this->info("✓ Backup do banco criado: {$backupDbPath}");
        }

        // Backup dos uploads
        $uploadsPath = storage_path('app/public');
        if (File::exists($uploadsPath)) {
            $backupUploadsPath = $backupDir . "/uploads_backup_{$timestamp}.zip";
            $this->zipDirectory($uploadsPath, $backupUploadsPath);
            $this->info("✓ Backup dos uploads criado: {$backupUploadsPath}");
        }

        // Registrar em audit_logs (se a tabela existir)
        try {
            DB::table('audit_logs')->insert([
                'admin_id' => auth()->id() ?? 1,
                'action' => 'backup_before_sample_cleanup',
                'detail' => "backup_db: db_backup_{$timestamp}.sqlite; backup_uploads: uploads_backup_{$timestamp}.zip",
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Tabela pode não existir, ignorar
        }
    }

    /**
     * Compactar diretório em ZIP
     */
    private function zipDirectory(string $source, string $destination): void
    {
        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($source) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
        }
    }

    /**
     * Marcar dados fictícios usando heurísticas
     */
    private function markSampleData(): void
    {
        $this->info('Marcando dados fictícios...');

        // Marcar transações fictícias
        $txnCount = DB::table('transactions')
            ->where(function ($query) {
                $query->where('invoice', 'like', 'TXN00%')
                    ->orWhereIn('total_amount', [1250.00, 850.00, 2100.00, 450.00, 3200.00])
                    ->orWhere('invoice', 'like', 'TXN%')
                    ->orWhere('acquirer_ref', 'like', 'PIX00%')
                    ->orWhere('acquirer_ref', 'like', 'TED00%')
                    ->orWhere('acquirer_ref', 'like', 'BOL00%');
            })
            ->update(['is_sample' => true]);

        $this->info("✓ {$txnCount} transações marcadas como fictícias");

        // Marcar produtos fictícios
        $productsCount = DB::table('products')
            ->where(function ($query) {
                $query->whereIn('name', [
                    'Curso de Programação Web',
                    'E-book Marketing Digital',
                    'Consultoria Personalizada',
                    'Kit Premium de Ferramentas',
                    'Template WordPress Pro'
                ])
                ->orWhere('name', 'like', '%exemplo%')
                ->orWhere('name', 'like', '%teste%')
                ->orWhere('name', 'like', '%demo%');
            })
            ->update(['is_sample' => true]);

        $this->info("✓ {$productsCount} produtos marcados como fictícios");

        // Marcar usuários fictícios (preservar admin)
        $usersCount = DB::table('users')
            ->where('role', '!=', 'admin')
            ->where(function ($query) {
                $query->where('email', 'like', '%vendedor%@example.com')
                    ->orWhere('email', 'like', '%@example.com')
                    ->orWhere('email', 'like', '%test%@%')
                    ->orWhere('email', 'like', '%demo%@%')
                    ->orWhere('name', 'like', 'Vendedor%')
                    ->orWhere('name', 'like', 'Teste%')
                    ->orWhere('name', 'like', 'Demo%');
            })
            ->update(['is_sample' => true]);

        $this->info("✓ {$usersCount} usuários marcados como fictícios");

        // Marcar saques fictícios (se a tabela existir)
        if (Schema::hasTable('withdrawals')) {
            $withdrawalsCount = DB::table('withdrawals')
                ->where(function ($query) {
                    $query->where('amount', '<=', 0)
                        ->orWhere('pix_key', 'like', '%exemplo%')
                        ->orWhere('pix_key', 'like', '%teste%');
                })
                ->update(['is_sample' => true]);

            $this->info("✓ {$withdrawalsCount} saques marcados como fictícios");
        }
    }

    /**
     * Deletar dados marcados como fictícios
     */
    private function deleteSampleData(): void
    {
        $this->info('Deletando dados fictícios...');

        // Contar antes de deletar
        $txnCount = DB::table('transactions')->where('is_sample', true)->count();
        $productsCount = DB::table('products')->where('is_sample', true)->count();
        $usersCount = DB::table('users')->where('is_sample', true)->where('role', '!=', 'admin')->count();
        $withdrawalsCount = Schema::hasTable('withdrawals') 
            ? DB::table('withdrawals')->where('is_sample', true)->count() 
            : 0;

        // Deletar transações
        DB::table('transactions')->where('is_sample', true)->delete();
        $this->info("✓ {$txnCount} transações deletadas");

        // Deletar produtos
        DB::table('products')->where('is_sample', true)->delete();
        $this->info("✓ {$productsCount} produtos deletados");

        // Deletar saques
        if (Schema::hasTable('withdrawals')) {
            DB::table('withdrawals')->where('is_sample', true)->delete();
            $this->info("✓ {$withdrawalsCount} saques deletados");
        }

        // Deletar usuários (preservar admin)
        DB::table('users')
            ->where('is_sample', true)
            ->where('role', '!=', 'admin')
            ->delete();
        $this->info("✓ {$usersCount} usuários deletados");

        // Registrar em audit_logs
        try {
            DB::table('audit_logs')->insert([
                'admin_id' => auth()->id() ?? 1,
                'action' => 'sample_data_cleanup',
                'detail' => "Transações: {$txnCount}, Produtos: {$productsCount}, Usuários: {$usersCount}, Saques: {$withdrawalsCount}",
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Tabela pode não existir, ignorar
        }
    }
}
