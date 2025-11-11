<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Checkout;
use Illuminate\Database\Seeder;

class CheckoutCheckIconColorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $checkouts = Checkout::all();

        foreach ($checkouts as $checkout) {
            $checkout->update([
                'pix_check_icon_background_color'         => '#ff0000', // Vermelho
                'credit_card_check_icon_background_color' => '#00ff00', // Verde
                'boleto_check_icon_background_color'      => '#0000ff', // Azul
            ]);
        }

        $this->command->info('Cores dos ícones de check atualizadas com sucesso!');
        $this->command->info('PIX: Vermelho (#ff0000)');
        $this->command->info('Cartão: Verde (#00ff00)');
        $this->command->info('Boleto: Azul (#0000ff)');
    }
}
