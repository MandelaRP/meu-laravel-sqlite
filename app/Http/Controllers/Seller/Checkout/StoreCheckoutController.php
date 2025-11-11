<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Checkout;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\Checkout\StoreCheckoutRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreCheckoutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreCheckoutRequest $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            /**
             * @var \App\Models\Checkout $checkout
            */
            $checkout = $user->checkouts()->create(Arr::except($request->validated(), 'order_bump_ids'));

            if ($request->has('order_bump_ids')) {
                foreach ($request->order_bump_ids as $order_bump_id) {
                    $checkout->orderBumps()->create([
                        'checkout_id' => $checkout->id,
                        'product_id'  => $order_bump_id,
                    ]);
                }
            }

            // Salvar banner se fornecido
            if ($request->hasFile('banner')) {
                $checkout->update([
                    'banner' => $request->file('banner')->store('checkouts/banners', 'public'),
                ]);
            }

            Log::info('Checkout criado com sucesso!', [
                'user_id'        => $user->id,
                'checkout_id'    => $checkout->id,
                'product_id'     => $request->product_id,
                'order_bump_ids' => $request->order_bump_ids,
            ]);

            return redirect()->route('checkout.index')->with('success', 'Checkout criado com sucesso!');
        } catch (\Throwable $th) {
            Log::error('Erro ao criar checkout: ' . $th->getMessage());

            return redirect()->back()->withErrors('error', 'Erro ao criar checkout: ' . $th->getMessage());
        }
    }
}
