<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Models\Seller\Product;
use App\Traits\SetLogTrait;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EditCheckoutController extends Controller
{
    use SetLogTrait;

    public function __invoke(Request $request, Checkout $checkout)
    {
        try {
            $user = $request->user();

            // Verificar se o checkout pertence ao usuário
            $checkout->load(['product.category', 'orderBumps']);

            $products = Product::where('status', true)
                ->with('category')
                ->get();

            return Inertia::render('Seller/Checkout/Edit', [
                'checkout' => $checkout,
                'products' => $products,
            ]);
        } catch (\Throwable $th) {
            $this->setLog(channel: 'error', message: 'Erro ao editar checkout!', data: $request->all(), type: 'error', error: $th);

            return redirect()->back()->withErrors('Oops! Houve um erro ao editar o checkout.', 'fatal');
        }
    }
}
