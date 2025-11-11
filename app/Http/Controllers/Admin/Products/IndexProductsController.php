<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Products;

use App\Http\Controllers\Controller;
use App\Models\Seller\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexProductsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $products = Product::with(['user'])
            ->where('is_sample', false)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($product) {
                // Calcular estatísticas reais (simplificado - usando checkouts como base)
                $totalSales = $product->checkout()->count();
                $totalRevenue = 0; // TODO: calcular quando houver relação direta com transactions
                $daysSinceCreation = max(1, now()->diffInDays($product->created_at));
                $dailyAverage = $totalSales > 0 ? ($totalRevenue / $daysSinceCreation) : 0;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description ?? '',
                    'sellerEmail' => $product->user->email ?? 'N/A',
                    'price' => (float) $product->price,
                    'image' => $product->image ? asset('storage/' . $product->image) : null,
                    'createdAt' => $product->created_at->format('Y-m-d H:i:s'),
                    'totalSales' => $totalSales,
                    'totalRevenue' => (float) $totalRevenue,
                    'dailyAverage' => (float) $dailyAverage,
                ];
            });

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
        ]);
    }
}
