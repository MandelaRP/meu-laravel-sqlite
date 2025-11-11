<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Checkout;
use Illuminate\Database\Seeder;

class UpdateCheckoutsFormFieldsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Atualizar todos os checkouts existentes com valores padrão
        Checkout::query()->update([
            'require_name'         => true,
            'require_email'        => true,
            'require_phone'        => false,
            'require_cpf_cnpj'     => false,
            'require_address'      => false,
            'require_cep'          => false,
            'require_city'         => false,
            'require_state'        => false,
            'require_neighborhood' => false,
            'require_number'       => false,
            'require_complement'   => false,
        ]);

        $this->command->info('Checkouts atualizados com configurações padrão do formulário!');
    }
}
