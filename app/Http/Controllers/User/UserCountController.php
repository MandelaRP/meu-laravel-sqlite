<?php

declare(strict_types = 1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserCountController extends Controller
{
    /**
     * Retorna contagem de usuários
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Incluir admin12@gmail.com mesmo se tiver is_sample = true
        // Excluir usuários recent_user (que não finalizaram o onboarding)
        $total = User::where('role', '!=', 'admin')
            ->where('status', '!=', 'recent_user')
            ->where(function ($query) {
                $query->where('is_sample', false)
                    ->orWhere('email', 'admin12@gmail.com');
            })
            ->count();
        $active = User::where('role', '!=', 'admin')
            ->where('status', '!=', 'recent_user')
            ->where(function ($query) {
                $query->where('is_sample', false)
                    ->orWhere('email', 'admin12@gmail.com');
            })
            ->where('status', 'active')
            ->count();
        $pending = User::where('role', '!=', 'admin')
            ->where('status', '!=', 'recent_user')
            ->where(function ($query) {
                $query->where('is_sample', false)
                    ->orWhere('email', 'admin12@gmail.com');
            })
            ->where('status', 'pending')
            ->count();
        $banned = User::where('role', '!=', 'admin')
            ->where('status', '!=', 'recent_user')
            ->where(function ($query) {
                $query->where('is_sample', false)
                    ->orWhere('email', 'admin12@gmail.com');
            })
            ->where('status', 'banned')
            ->count();

        return response()->json([
            'success' => true,
            'count' => [
                'total' => $total,
                'active' => $active,
                'pending' => $pending,
                'banned' => $banned,
            ],
        ]);
    }
}

