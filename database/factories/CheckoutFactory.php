<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\Seller\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Checkout>
 */
class CheckoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id'        => Product::factory(),
            'checkout_template' => fake()->randomElement(['default', 'minimal', 'premium', 'custom']),
        ];
    }
}
