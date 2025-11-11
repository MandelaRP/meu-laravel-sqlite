<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Enums\Seller\ProductTypeEnum;
use App\Models\Seller\Category;
use App\Models\Seller\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            $this->command->error('Nenhum usuário encontrado. Execute o UserSeeder primeiro.');

            return;
        }

        // Buscar categorias existentes ou criar uma padrão
        $ebookCategory       = Category::where('name', 'Ebooks')->first();
        $eletronicosCategory = Category::where('name', 'Eletrônicos')->first();

        if (! $ebookCategory) {
            $ebookCategory = Category::create([
                'user_id'     => $user->id,
                'name'        => 'Ebooks',
                'description' => 'Ebooks e materiais de leitura',
                'status'      => true,
            ]);
        }

        if (! $eletronicosCategory) {
            $eletronicosCategory = Category::create([
                'user_id'     => $user->id,
                'name'        => 'Eletrônicos',
                'description' => 'Produtos eletrônicos e tecnológicos',
                'status'      => true,
            ]);
        }

        $products = [
            [
                'user_id'     => $user->id,
                'category_id' => $ebookCategory->id,
                'name'        => 'Guia Completo de Marketing Digital',
                'description' => 'Um ebook completo com estratégias avançadas de marketing digital, incluindo SEO, mídias sociais, email marketing e análise de dados. Ideal para empreendedores e profissionais de marketing.',
                'image'       => null,
                'status'      => true,
                'type'        => ProductTypeEnum::DIGITAL,
                'price'       => 97.00,
                'stock'       => 0, // Produto digital não tem estoque físico
            ],
            [
                'user_id'     => $user->id,
                'category_id' => $eletronicosCategory->id,
                'name'        => 'Smartphone Galaxy S23',
                'description' => 'Smartphone Samsung Galaxy S23 com 128GB de armazenamento, câmera de 50MP, tela de 6.1" e processador Snapdragon 8 Gen 2. Inclui carregador e fone de ouvido.',
                'image'       => null,
                'status'      => true,
                'type'        => ProductTypeEnum::FISICAL,
                'price'       => 2499.00,
                'stock'       => 15,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        $this->command->info('Produtos criados com sucesso!');
        $this->command->info('Produtos criados:');

        foreach ($products as $product) {
            $this->command->info("- {$product['name']} - R$ " . number_format($product['price'], 2, ',', '.'));
        }
    }
}
