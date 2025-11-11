<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Models\Seller\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexCheckoutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $products = Product::where('user_id', $user->id)
            ->where('status', true)
            ->with('category')
            ->get();

        $checkouts = Checkout::whereHas('product', function ($query) use ($user): void {
            $query->where('user_id', $user->id);
        })->with('product.category')->get();

        return Inertia::render('Seller/Checkout/Index', [
            'products'  => $products,
            'checkouts' => $checkouts,
        ]);
    }
}
