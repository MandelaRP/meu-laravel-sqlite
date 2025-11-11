<?php

declare(strict_types = 1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        try {
            [$message, $author] = str(Inspiring::quotes()->random())->explode('-');
        } catch (\Throwable $e) {
            $message = 'Welcome to LuckPay';
            $author = 'System';
        }

        $user = $request->user();
        $pendingWithdrawals = 0;
        $pendingUsers = 0;
        $faturamento = 0;

        // Buscar faturamento atual se usuário estiver autenticado
        if ($user) {
            try {
                $faturamento = \App\Models\Transaction::where('user_id', $user->id)
                    ->where('payment_status', 'Paid')
                    ->where('is_sample', false)
                    ->sum('total_amount');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Erro ao buscar faturamento no HandleInertiaRequests: ' . $e->getMessage());
                $faturamento = 0;
            }
        }

        // Só fazer consultas se o usuário estiver autenticado e for admin
        if ($user && $user->role === 'admin') {
            try {
                // Contar saques pendentes
                if (\Illuminate\Support\Facades\Schema::hasTable('withdrawals')) {
                    if (\Illuminate\Support\Facades\Schema::hasColumn('withdrawals', 'is_sample')) {
                        $pendingWithdrawals = \Illuminate\Support\Facades\DB::table('withdrawals')
                            ->where('is_sample', false)
                            ->where('status', 'pendente')
                            ->count();
                    } else {
                        $pendingWithdrawals = \Illuminate\Support\Facades\DB::table('withdrawals')
                            ->where('status', 'pendente')
                            ->count();
                    }
                } else {
                    // Fallback: usar transações
                    $query = \App\Models\Transaction::where('payment_method', 'like', '%saque%')
                        ->where('payment_status', 'Pending');
                    
                    if (\Illuminate\Support\Facades\Schema::hasColumn('transactions', 'is_sample')) {
                        $query->where('is_sample', false);
                    }
                    
                    $pendingWithdrawals = $query->count();
                }

                // Contar usuários pendentes
                // Excluir usuários recent_user (já está implícito pois status = 'pending')
                $userQuery = \App\Models\User::where('status', 'pending')
                    ->where('role', '!=', 'admin');
                
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_sample')) {
                    $userQuery->where(function ($q) {
                        $q->where('is_sample', false)
                          ->orWhere('email', 'admin12@gmail.com');
                    });
                }
                
                $pendingUsers = $userQuery->count();
            } catch (\Throwable $e) {
                // Em caso de erro, manter valores em 0
                \Illuminate\Support\Facades\Log::error('Erro ao buscar contadores no HandleInertiaRequests: ' . $e->getMessage());
                $pendingWithdrawals = 0;
                $pendingUsers = 0;
            }
        }

        return [
            ...parent::share($request),
            'name'  => config('app.name'),
            'quote' => ['message' => trim((string) $message), 'author' => trim((string) $author)],
            'auth'  => [
                'user' => $user,
            ],
            'is_admin' => $user && $user->role === 'admin',
            'pendingWithdrawals' => $pendingWithdrawals,
            'pendingUsers' => $pendingUsers,
            'stats' => [
                'faturamento' => (float) $faturamento,
            ],
        ];
    }
}
