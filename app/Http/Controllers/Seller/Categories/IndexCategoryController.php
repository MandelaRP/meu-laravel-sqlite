<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Categories;

use App\Http\Controllers\Controller;
use App\Models\Seller\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexCategoryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->get();

        return Inertia::render('Seller/Categories/Index', [
            'categories' => $categories,
        ]);
    }
}
