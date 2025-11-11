<?php

declare(strict_types = 1);

namespace Database\Factories\Seller;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Seller\Category>
 */
class CategoryFactory extends Factory
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
            'name'        => fake()->words(2, true),
            'description' => fake()->sentence(),
            'icon'        => fake()->randomElement(['shopping-bag', 'gift', 'star', 'heart', 'trending-up']),
            'status'      => true,
        ];
    }
}
