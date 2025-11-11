<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Checkout;
use Illuminate\Database\Seeder;

class CheckoutPaymentMethodSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $checkouts = Checkout::all();

        foreach ($checkouts as $checkout) {
            $checkout->update([
                'payment_methods' => [
                    [
                        'name'          => 'pix',
                        'label'         => 'PIX',
                        'icon'          => 'pix',
                        'image'         => '/images/icons/icon-pix.png',
                        'show_image'    => true,
                        'icon_color'    => '#ffffff',
                        'icon_bg_color' => '#10b981',
                        'enabled'       => true,
                    ],
                    [
                        'name'          => 'credit_card',
                        'label'         => 'Cartão de Crédito',
                        'icon'          => 'credit_card',
                        'image'         => '/images/icons/icon-credit-card.png',
                        'show_image'    => false,
                        'icon_color'    => '#ffffff',
                        'icon_bg_color' => '#3b82f6',
                        'enabled'       => true,
                    ],
                    [
                        'name'          => 'boleto',
                        'label'         => 'Boleto',
                        'icon'          => 'boleto',
                        'image'         => '/images/icons/icon-boleto.png',
                        'show_image'    => false,
                        'icon_color'    => '#ffffff',
                        'icon_bg_color' => '#f97316',
                        'enabled'       => true,
                    ],
                ],
            ]);
        }

        $this->command->info('Configurações dos métodos de pagamento atualizadas para todos os checkouts.');
    }
}
