<?php

declare(strict_types=1);

namespace App\Http\Controllers\User\PixKey;

use App\Http\Controllers\Controller;
use App\Models\PixKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdatePixKeyController extends Controller
{
    public function __invoke(Request $request, int $id): RedirectResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $pixKey = PixKey::where('user_id', $user->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:CPF,CNPJ,EMAIL,PHONE,EVP',
            'key' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validar formato da chave baseado no tipo
        $type = $request->input('type');
        $key = $request->input('key');
        
        if (!$this->validatePixKey($type, $key)) {
            return redirect()->back()
                ->withErrors(['key' => 'Formato da chave PIX inválido para o tipo selecionado.'])
                ->withInput();
        }

        $pixKey->update([
            'type' => $type,
            'key' => $key,
            'description' => $request->input('description'),
        ]);

        return redirect()->back()->with('success', 'Chave PIX atualizada com sucesso!');
    }

    private function validatePixKey(string $type, string $key): bool
    {
        return match($type) {
            'CPF' => preg_match('/^\d{11}$/', preg_replace('/[^0-9]/', '', $key)),
            'CNPJ' => preg_match('/^\d{14}$/', preg_replace('/[^0-9]/', '', $key)),
            'EMAIL' => filter_var($key, FILTER_VALIDATE_EMAIL) !== false,
            'PHONE' => preg_match('/^\+55\d{10,11}$/', $key) || preg_match('/^\d{10,11}$/', preg_replace('/[^0-9]/', '', $key)),
            'EVP' => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $key),
            default => false,
        };
    }
}
