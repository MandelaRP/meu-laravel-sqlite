<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Products;

use App\Http\Controllers\Controller;
use App\Models\Seller\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexProductController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $query = Product::where('user_id', $user->id);

        // Filtros
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request): void {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('type') && $request->type && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->has('status') && $request->status !== null && $request->status !== 'all') {
            $query->where('status', $request->status === 'active');
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10);

        return Inertia::render('Seller/Products/Index', [
            'products' => $products,
            'filters'  => [
                'search' => $request->search ?? '',
                'type'   => $request->type ?? 'all',
                'status' => $request->status ?? 'all',
            ],
        ]);
    }
}

