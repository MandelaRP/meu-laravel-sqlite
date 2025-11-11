<?php

declare(strict_types = 1);

namespace Database\Factories\Seller;

use App\Enums\Seller\ProductTypeEnum;
use App\Models\Seller\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Seller\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'category_id' => Category::factory(),
            'name'        => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'image'       => fake()->imageUrl(),
            'status'      => true,
            'type'        => fake()->randomElement(ProductTypeEnum::cases()),
            'price'       => fake()->randomFloat(2, 10, 1000),
            'stock'       => fake()->numberBetween(0, 100),
        ];
    }
}
