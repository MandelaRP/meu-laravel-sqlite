<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Products;

use App\Http\Controllers\Controller;
use App\Models\Seller\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class DestroyProductController extends Controller
{
    public function __invoke(Product $product): RedirectResponse
    {
        try {
            // Deletar checkouts relacionados primeiro (para evitar erro de foreign key)
            $product->checkout()->delete();
            
            // Remove imagem se existir
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return redirect()->route('admin.products.index')
                ->with('success', 'Produto excluído com sucesso!');
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('error', 'Erro ao excluir produto: ' . $th->getMessage());
        }
    }
}

