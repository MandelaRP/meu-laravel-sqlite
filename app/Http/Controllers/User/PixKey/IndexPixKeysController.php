<?php

declare(strict_types=1);

namespace App\Http\Controllers\User\PixKey;

use App\Http\Controllers\Controller;
use App\Models\PixKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexPixKeysController extends Controller
{
    public function __invoke(Request $request): JsonResponse|Response
    {
        $user = auth()->user();
        
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Não autenticado'], 401);
            }
            return redirect()->route('login');
        }

        $pixKeys = PixKey::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // Se for requisição AJAX/JSON, retornar JSON
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'pixKeys' => $pixKeys,
            ]);
        }

        // Caso contrário, retornar view Inertia
        return Inertia::render('User/PixKey/Index', [
            'pixKeys' => $pixKeys,
        ]);
    }
}
