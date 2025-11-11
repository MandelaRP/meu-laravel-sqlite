<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Checkout;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\Checkout\UpdateCheckoutRequest;
use App\Models\Checkout;
use App\Traits\SetLogTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UpdateCheckoutController extends Controller
{
    use SetLogTrait;

    public function __invoke(UpdateCheckoutRequest $request, Checkout $checkout): RedirectResponse
    {
        $data = $request->validated();

        try {
            // Processar banner
            $this->processBanner($request, $checkout, $data);

            $checkout->orderBumps()->delete();

            if ($request->has('order_bump_ids')) {
                foreach ($data['order_bump_ids'] as $productId) {
                    $checkout->orderBumps()->create([
                        'product_id' => $productId,
                    ]);
                }
                unset($data['order_bump_ids']);
            }
            
            // IMPORTANTE: Preservar form_fields_config exatamente como enviado
            // Não processar, não mesclar, não adicionar campos padrão
            if (isset($data['form_fields_config'])) {
                // Garantir que seja um array válido
                if (is_array($data['form_fields_config'])) {
                    // Preservar exatamente como está, sem modificações
                    // Garantir que valores booleanos sejam preservados corretamente
                    $data['form_fields_config'] = array_map(function ($field) {
                        if (is_array($field)) {
                            // Garantir que visible e required sejam booleanos, não strings
                            if (isset($field['visible'])) {
                                $field['visible'] = filter_var($field['visible'], FILTER_VALIDATE_BOOLEAN);
                            }
                            if (isset($field['required'])) {
                                $field['required'] = filter_var($field['required'], FILTER_VALIDATE_BOOLEAN);
                            }
                        }
                        return $field;
                    }, $data['form_fields_config']);
                } else {
                    // Se não for array válido, manter o existente
                    unset($data['form_fields_config']);
                }
            }
            
            $checkout->update($data);

            return redirect()->back()
                ->with('success', 'Checkout atualizado com sucesso!');
        } catch (\Throwable $th) {
            $this->setLog(channel: 'error', message: 'Erro ao atualizar checkout!', data: $request->all(), type: 'error', error: $th);

            return redirect()->back()->withErrors('Oops! Houve um erro ao atualizar o checkout.', 'fatal');
        }
    }

    /**
     * Processar upload/remoção do banner
     */
    private function processBanner(Request $request, Checkout $checkout, array &$data): void
    {
        if ($request->hasFile('banner')) {
            Log::info('Processando upload de banner');

            // Remove banner antigo se existir
            if ($checkout->banner && Storage::disk('public')->exists($checkout->banner)) {
                Storage::disk('public')->delete($checkout->banner);
                Log::info('Banner antigo removido', ['path' => $checkout->banner]);
            }
            $data['banner'] = $request->file('banner')->store('checkouts/banners', 'public');
            Log::info('Banner salvo', ['path' => $data['banner']]);
        } elseif ($request->input('banner') === 'remove' || $request->input('banner') === null) {
            Log::info('Removendo banner', [
                'input' => $request->input('banner'),
                'has_file' => $request->hasFile('banner'),
            ]);

            // Remove o banner existente se o usuário escolheu remover
            if ($checkout->banner && Storage::disk('public')->exists($checkout->banner)) {
                Storage::disk('public')->delete($checkout->banner);
                Log::info('Banner removido do storage', ['path' => $checkout->banner]);
            }
            $data['banner'] = null;
            Log::info('Banner removido do banco');
        } else {
            // Se não foi enviado banner e não foi solicitada remoção, manter o existente
            if ($checkout->banner) {
                $data['banner'] = $checkout->banner;
                Log::info('Banner mantido', ['path' => $checkout->banner]);
            }
        }
    }
}
