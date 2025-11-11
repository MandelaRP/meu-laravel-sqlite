<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\Products\UpdateProductRequest;
use App\Models\Seller\Product;
use Illuminate\Support\Facades\Storage;

class UpdateProductController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateProductRequest $request, Product $product)
    {
        try {
            $data = $request->validated();

            // Gerenciar imagem apenas se foi explicitamente enviada
            if ($request->hasFile('image')) {
                // Nova imagem foi enviada - fazer upload
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $request->file('image')->store('products', 'public');
            } elseif ($request->has('image') && $request->input('image') === 'remove') {
                // Imagem foi explicitamente removida
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = null;
            } else {
                // Se image não foi enviado no request (nem arquivo nem 'remove'), 
                // remover do array $data para manter a imagem existente
                // O Laravel só atualiza os campos que estão no array $data
                unset($data['image']);
            }

            // Atualizar apenas os campos que foram enviados
            $product->update($data);

            return redirect()->route('products.index')->with('success', 'Produto atualizado com sucesso!');
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors(['error' => 'Erro ao atualizar produto: ' . $th->getMessage()]);
        }
    }
}

