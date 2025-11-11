<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar se já existe um admin
        $adminExists = User::where('email', 'admin@luckpay.com')->exists();

        if (!$adminExists) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@luckpay.com',
                'password' => Hash::make('password'), // Senha padrão: password
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
                'is_sample' => false,
            ]);

            $this->command->info('Usuário admin criado com sucesso!');
            $this->command->info('Email: admin@luckpay.com');
            $this->command->info('Senha: password');
        } else {
            $this->command->info('Usuário admin já existe.');
        }
    }
}
