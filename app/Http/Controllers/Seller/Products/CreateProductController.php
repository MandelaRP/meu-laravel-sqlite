<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreateProductController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return Inertia::render('Seller/Products/Create');
    }
}

