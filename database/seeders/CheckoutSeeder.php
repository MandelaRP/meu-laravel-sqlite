<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Checkout;
use App\Models\Seller\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class CheckoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            return;
        }

        // Criar produtos de exemplo
        $products = Product::factory(3)->create([
            'user_id' => $user->id,
            'status'  => true,
        ]);

        // Criar checkouts de exemplo
        foreach ($products as $index => $product) {
            Checkout::create([
                'user_id'              => $user->id,
                'product_id'           => $product->id,
                'checkout_template'    => 'default',
                'layout'               => 'single',
                'countdown_enabled'    => $index % 2 === 0, // Habilitar para checkouts pares
                'countdown_icon'       => $index % 2 === 0 ? 'fire' : 'clock',
                'countdown_icon_type'  => $index % 2 === 0 ? 'emoji' : 'icon', // Emoji para pares, ícone para ímpares
                'countdown_duration'   => $index % 2 === 0 ? 3600 : 7200, // 1 hora ou 2 horas
                'countdown_bg_color'   => $index % 2 === 0 ? '#dc2626' : '#059669', // Vermelho ou verde
                'countdown_text_color' => '#ffffff',
                'countdown_message'    => $index % 2 === 0 ? 'Oferta por tempo limitado!' : 'Promoção especial!',
                'countdown_start_time' => now(),
                'countdown_expired'    => $index % 4 === 0, // Marcar como expirado para alguns checkouts
                'dark_mode'            => $index % 3 === 0, // Habilitar dark mode para alguns checkouts
                'banner'               => $index % 3 === 0 ? 'checkouts/banners/banner-' . $index . '.jpg' : null,
            ]);
        }
    }
}
