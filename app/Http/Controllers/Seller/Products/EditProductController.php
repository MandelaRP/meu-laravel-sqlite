<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Products;

use App\Http\Controllers\Controller;
use App\Models\Seller\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EditProductController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Product $product)
    {
        return Inertia::render('Seller/Products/Edit', [
            'product' => $product,
        ]);
    }
}

