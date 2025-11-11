<?php

declare(strict_types = 1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CleanUsersCommand extends Command
{
    protected $signature = 'users:clean {--keep-email= : Email do usuário a manter}';
    protected $description = 'Remove todos os usuários exceto o especificado';

    public function handle(): int
    {
        $keepEmail = $this->option('keep-email') ?? 'admin12@gmail.com';
        
        $keepUser = User::where('email', $keepEmail)->first();
        
        if (!$keepUser) {
            $this->error("Usuário com email {$keepEmail} não encontrado!");
            return 1;
        }

        $this->info("Mantendo usuário: {$keepUser->name} ({$keepUser->email})");
        
        // Contar usuários a serem deletados
        $usersToDelete = User::where('id', '!=', $keepUser->id)
            ->where('is_sample', false)
            ->count();
        
        if ($usersToDelete === 0) {
            $this->info('Nenhum usuário para deletar.');
            return 0;
        }

        if (!$this->confirm("Deseja deletar {$usersToDelete} usuário(s)?", true)) {
            $this->info('Operação cancelada.');
            return 0;
        }

        // Desabilitar temporariamente foreign key checks para SQLite
        if (config('database.default') === 'sqlite') {
            \DB::statement('PRAGMA foreign_keys = OFF');
        }

        try {
            // Deletar usuários (cascade vai deletar transações)
            $deleted = User::where('id', '!=', $keepUser->id)
                ->where('is_sample', false)
                ->delete();
        } finally {
            // Reabilitar foreign key checks
            if (config('database.default') === 'sqlite') {
                \DB::statement('PRAGMA foreign_keys = ON');
            }
        }

        $this->info("✓ {$deleted} usuário(s) deletado(s) com sucesso!");
        $this->info("Usuário mantido: {$keepUser->name} ({$keepUser->email})");

        return 0;
    }
}

