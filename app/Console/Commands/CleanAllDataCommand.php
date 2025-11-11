<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Seller\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanAllDataCommand extends Command
{
    protected $signature = 'clean:all-data';
    protected $description = 'Limpa todos os dados e deixa apenas admin12@gmail.com como ID 1';

    public function handle(): int
    {
        $this->info('Limpando todos os dados...');

        $driver = DB::getDriverName();
        
        // Desabilitar foreign key checks temporariamente
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        try {
            // 1. Deletar todos os produtos (deletar checkouts primeiro se houver foreign key)
            try {
                DB::table('checkouts')->delete();
            } catch (\Exception $e) {
                // Ignorar se não existir
            }
            
            $productsCount = Product::count();
            Product::query()->delete();
            $this->info("✓ {$productsCount} produtos deletados");

            // 2. Deletar todos os usuários exceto admin12@gmail.com
            $adminUser = User::where('email', 'admin12@gmail.com')->first();
            
            if ($adminUser) {
                // Deletar todos os outros usuários
                $deletedCount = User::where('id', '!=', $adminUser->id)->delete();
                $this->info("✓ {$deletedCount} usuários deletados");
                
                // Resetar o ID do admin para 1 se não for (apenas MySQL)
                if ($adminUser->id !== 1 && $driver === 'mysql') {
                    // Deletar temporariamente para resetar ID
                    $adminData = $adminUser->toArray();
                    $adminUser->delete();
                    
                    // Criar novamente com ID 1
                    $adminData['id'] = 1;
                    unset($adminData['created_at'], $adminData['updated_at']);
                    User::create($adminData);
                    $this->info("✓ Admin resetado para ID 1");
                } else {
                    $this->info("✓ Admin mantido (ID: {$adminUser->id})");
                }
            } else {
                $this->warn("⚠ Admin admin12@gmail.com não encontrado. Todos os usuários serão deletados.");
                User::query()->delete();
                $this->info("✓ Todos os usuários deletados");
            }

            // 3. Resetar auto increment (apenas MySQL)
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE users AUTO_INCREMENT = 2');
                DB::statement('ALTER TABLE products AUTO_INCREMENT = 1');
                $this->info('✓ Auto increment resetado');
            }

        } catch (\Exception $e) {
            $this->error('Erro ao limpar dados: ' . $e->getMessage());
            return 1;
        } finally {
            // Reabilitar foreign key checks
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            }
        }

        $this->info('✓ Limpeza concluída com sucesso!');
        return 0;
    }
}

