<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Seller\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreateCheckoutController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $products = Product::where('user_id', $user->id)
            ->where('status', true)
            ->with('category')
            ->get();

        // Redirecionar se não houver produtos
        if ($products->isEmpty()) {
            return redirect()->route('products.index')
                ->with('error', 'Você precisa criar pelo menos um produto antes de criar um checkout.');
        }

        return Inertia::render('Seller/Checkout/Create', [
            'products' => $products,
        ]);
    }
}
