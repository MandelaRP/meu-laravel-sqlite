<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Seller\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
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

        $categories = [
            [
                'user_id'     => $user->id,
                'name'        => 'Eletrônicos',
                'description' => 'Produtos eletrônicos e tecnológicos',
                'status'      => true,
            ],
            [
                'user_id'     => $user->id,
                'name'        => 'Ebooks',
                'description' => 'Ebooks e materiais de leitura',
                'status'      => true,
            ],
            [
                'user_id'     => $user->id,
                'name'        => 'Vestuário',
                'description' => 'Roupas e acessórios',
                'status'      => true,
            ],
            [
                'user_id'     => $user->id,
                'name'        => 'Casa e Jardim',
                'description' => 'Produtos para casa e jardinagem',
                'status'      => true,
            ],
            [
                'user_id'     => $user->id,
                'name'        => 'Esportes',
                'description' => 'Equipamentos e acessórios esportivos',
                'status'      => true,
            ],
            [
                'user_id'     => $user->id,
                'name'        => 'Livros',
                'description' => 'Livros e materiais de leitura',
                'status'      => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
