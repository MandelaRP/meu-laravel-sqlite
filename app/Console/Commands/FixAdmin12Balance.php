<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FixAdmin12Balance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:admin12-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove o saldo incorreto de 7,72 do admin12@gmail.com';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::where('email', 'admin12@gmail.com')->first();

        if (!$user) {
            $this->error('Usuário admin12@gmail.com não encontrado!');
            return Command::FAILURE;
        }

        $currentBalance = $user->balance ?? 0;
        $this->info("Saldo atual do admin12@gmail.com: R$ " . number_format($currentBalance, 2, ',', '.'));

        // Remover 7,72 do saldo
        $newBalance = max(0, $currentBalance - 7.72);
        $user->update(['balance' => $newBalance]);

        $this->info("Saldo corrigido: R$ " . number_format($newBalance, 2, ',', '.'));
        $this->info("Removido: R$ 7,72");

        return Command::SUCCESS;
    }
}
