<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Checkout;
use Illuminate\Database\Seeder;

class CheckoutBackgroundColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cores de fundo para testar
        $backgroundColors = [
            '#f8fafc', // Cinza claro (padrão)
            '#ffffff', // Branco
            '#f0f9ff', // Azul muito claro
            '#fef3c7', // Amarelo claro
            '#fce7f3', // Rosa claro
            '#ecfdf5', // Verde claro
            '#fef2f2', // Vermelho claro
            '#f5f3ff', // Roxo claro
        ];

        // Atualizar checkouts existentes com cores diferentes
        $checkouts = Checkout::all();

        foreach ($checkouts as $index => $checkout) {
            $colorIndex = $index % count($backgroundColors);
            $checkout->update([
                'background_color' => $backgroundColors[$colorIndex],
            ]);
        }

        $this->command->info('Checkouts atualizados com cores de fundo diferentes!');
    }
}
