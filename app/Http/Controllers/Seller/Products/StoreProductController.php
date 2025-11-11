<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\Products\StoreProductRequest;
use App\Models\Seller\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StoreProductController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreProductRequest $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $data = $request->validated();
            $data['user_id'] = $user->id;
            $data['status'] = true; // Todos os produtos criados são ativos por padrão

            // Upload da imagem se fornecida
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            Product::create($data);

            return redirect()->route('products.index')->with('success', 'Produto criado com sucesso!');
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors(['error' => 'Erro ao criar produto: ' . $th->getMessage()]);
        }
    }
}

