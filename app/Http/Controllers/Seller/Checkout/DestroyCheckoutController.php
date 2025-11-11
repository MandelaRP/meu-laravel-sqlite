<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Traits\SetLogTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DestroyCheckoutController extends Controller
{
    use SetLogTrait;

    public function __invoke(Request $request, Checkout $checkout): RedirectResponse
    {
        try {
            $user = $request->user();

            // Verificar se o checkout pertence ao usuário
            if ($checkout->product->user_id !== $user->id) {
                abort(403, 'Acesso negado');
            }

            // Remove banner se existir
            if ($checkout->banner && Storage::disk('public')->exists($checkout->banner)) {
                Storage::disk('public')->delete($checkout->banner);
            }

            $checkout->delete();

            return redirect()->route('checkout.index')
                ->with('success', 'Checkout excluído com sucesso!');
        } catch (\Throwable $th) {
            $this->setLog(channel: 'error', message: 'Erro ao excluir checkout!', data: $request->all(), type: 'error', error: $th);

            return redirect()->back()->withErrors('Oops! Houve um erro ao excluir o checkout.', 'fatal');
        }
    }
}
