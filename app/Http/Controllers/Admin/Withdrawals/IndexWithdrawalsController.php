<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Withdrawals;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class IndexWithdrawalsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        // Se a tabela withdrawals não existir, usar transações como saques
        $hasWithdrawalsTable = DB::getSchemaBuilder()->hasTable('withdrawals');
        
        if ($hasWithdrawalsTable) {
            $withdrawals = DB::table('withdrawals')
                ->where('withdrawals.is_sample', false)
                ->join('users', 'withdrawals.user_id', '=', 'users.id')
                ->select(
                    'withdrawals.id',
                    'users.email',
                    'withdrawals.pix_key as pixKey',
                    'withdrawals.pix_type as pixType',
                    'withdrawals.amount',
                    'withdrawals.fee',
                    'withdrawals.net_amount',
                    'withdrawals.created_at as date',
                    'withdrawals.paid_at',
                    'withdrawals.status'
                )
                ->orderBy('withdrawals.created_at', 'desc')
                ->get()
                ->map(function ($withdrawal) {
                    return [
                        'id' => $withdrawal->id,
                        'email' => $withdrawal->email,
                        'pixKey' => $withdrawal->pixKey ?? 'N/A',
                        'pixType' => $withdrawal->pixType ?? 'N/A',
                        'amount' => (float) $withdrawal->amount,
                        'fee' => (float) ($withdrawal->fee ?? 0),
                        'net_amount' => (float) ($withdrawal->net_amount ?? 0),
                        'date' => $withdrawal->date,
                        'paid_at' => $withdrawal->paid_at,
                        'status' => $this->mapStatus($withdrawal->status ?? 'pending'),
                    ];
                });
        } else {
            // Fallback: usar transações como saques (quando payment_method contém 'saque')
            $withdrawals = DB::table('transactions')
                ->where('transactions.is_sample', false)
                ->where('transactions.payment_method', 'like', '%saque%')
                ->join('users', 'transactions.user_id', '=', 'users.id')
                ->select(
                    'transactions.id',
                    'users.email',
                    DB::raw("'N/A' as pixKey"),
                    DB::raw("'N/A' as pixType"),
                    'transactions.total_amount as amount',
                    'transactions.created_at as date',
                    'transactions.payment_status as status'
                )
                ->orderBy('transactions.created_at', 'desc')
                ->get()
                ->map(function ($withdrawal) {
                    return [
                        'id' => $withdrawal->id,
                        'email' => $withdrawal->email,
                        'pixKey' => $withdrawal->pixKey ?? 'N/A',
                        'pixType' => $withdrawal->pixType ?? 'N/A',
                        'amount' => (float) $withdrawal->amount,
                        'date' => $withdrawal->date,
                        'status' => $this->mapStatus($withdrawal->status ?? 'pending'),
                    ];
                });
        }

        // Calcular estatísticas
        // Incluir status "done" e "done_manual" como aprovados
        $aprovados = $withdrawals->filter(function ($w) {
            $status = strtolower($w['status']);
            return in_array($status, ['aprovado', 'done', 'done_manual', 'finalizado', 'paid', 'approved']);
        });
        
        $pendentes = $withdrawals->filter(function ($w) {
            $status = strtolower($w['status']);
            return in_array($status, ['pendente', 'pending', 'processing', 'processando']);
        });
        
        $cancelados = $withdrawals->filter(function ($w) {
            $status = strtolower($w['status']);
            return in_array($status, ['cancelado', 'cancelled', 'unpaid', 'failed', 'refused']);
        });
        
        $summaryStats = [
            'totalAprovados' => $aprovados->sum('amount'),
            'quantidadeAprovados' => $aprovados->count(),
            'totalPendentes' => $pendentes->sum('amount'),
            'quantidadePendentes' => $pendentes->count(),
            'totalCancelados' => $cancelados->sum('amount'),
            'quantidadeCancelados' => $cancelados->count(),
            // Contar usuários únicos que solicitaram saque
            'quantidadeUsuarios' => $withdrawals->pluck('email')->unique()->count(),
        ];

        // Buscar modo de transferência das configurações
        $transferMode = SystemSetting::get('transfer_mode', 'manual');
        $saqueAutomatico = ($transferMode === 'automatico' || $transferMode === 'Saque Automático');

        // Paginar saques
        $page = $request->get('page', 1);
        $perPage = 10;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $withdrawals->values()->forPage($page, $perPage),
            $withdrawals->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Admin/Withdrawals/Index', [
            'withdrawals' => $paginated,
            'summaryStats' => $summaryStats,
            'saqueAutomatico' => $saqueAutomatico,
        ]);
    }

    private function mapStatus(string $status): string
    {
        return match(strtolower($status)) {
            'paid', 'approved', 'aprovado', 'done', 'done_manual', 'finalizado' => 'aprovado',
            'processing', 'processando' => 'pendente',
            'pending', 'pendente' => 'pendente',
            'unpaid', 'cancelled', 'cancelado', 'failed', 'refused' => 'cancelado',
            default => 'pendente',
        };
    }
}
