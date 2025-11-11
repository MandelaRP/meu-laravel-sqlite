<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShowCheckoutController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $id)
    {
        $checkout = Checkout::with('product', 'orderBumps.product')->where('id', $id)->firstOrFail();

        //dd($checkout['form_fields_config']);

        return Inertia::render('Checkout/Show', [
            'checkout' => $checkout,
        ]);
    }
}
