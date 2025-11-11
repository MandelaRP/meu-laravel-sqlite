<?php

declare(strict_types=1);

namespace App\Http\Controllers\User\PixKey;

use App\Http\Controllers\Controller;
use App\Models\PixKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyPixKeyController extends Controller
{
    public function __invoke(Request $request, int $id): RedirectResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $pixKey = PixKey::where('user_id', $user->id)->findOrFail($id);
        $pixKey->delete();

        return redirect()->back()->with('success', 'Chave PIX excluída com sucesso!');
    }
}
