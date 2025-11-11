<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserStatsController extends Controller
{
    /**
     * Retorna estatísticas do usuário
     */
    public function __invoke(Request $request, User $user): JsonResponse
    {
        try {
            // Saldo atual
            $saldoAtual = $user->balance ?? 0;

            // Incluir transações do admin12@gmail.com mesmo se tiverem is_sample = true
            $transactionQuery = Transaction::where('user_id', $user->id);
            if ($user->email === 'admin12@gmail.com') {
                // Para admin12@gmail.com, incluir todas as transações
                $transactionQuery->where(function ($q) {
                    $q->where('is_sample', false)->orWhere('is_sample', true);
                });
            } else {
                $transactionQuery->where('is_sample', false);
            }

            // Volume transacionado
            $volumeTransacionado = (clone $transactionQuery)
                ->where('payment_status', 'Paid')
                ->sum('total_amount') ?? 0;

            // Total de transações
            $transacoesTotal = (clone $transactionQuery)->count();

            // Transações aprovadas
            $transacoesAprovadas = (clone $transactionQuery)
                ->where('payment_status', 'Paid')
                ->count();

            // Depósitos aprovados
            $depositosAprovados = (clone $transactionQuery)
                ->where('payment_status', 'Paid')
                ->sum('total_amount') ?? 0;

            // Depósitos líquidos
            $depositosLiquidos = (clone $transactionQuery)
                ->where('payment_status', 'Paid')
                ->sum('net_deposit') ?? 0;

            // PIX gerados (transações com método PIX)
            $pixGerados = (clone $transactionQuery)
                ->where('payment_method', 'like', '%pix%')
                ->where('payment_status', 'Paid')
                ->count();

            // Lucro da plataforma (taxas)
            $lucroPlataforma = (clone $transactionQuery)
                ->where('payment_status', 'Paid')
                ->sum('fee') ?? 0;

            return response()->json([
                'success' => true,
                'stats' => [
                    'saldo_atual' => (float) $saldoAtual,
                    'volume_transacionado' => (float) $volumeTransacionado,
                    'transacoes_total' => $transacoesTotal,
                    'transacoes_aprovadas' => $transacoesAprovadas,
                    'depositos_aprovados' => (float) $depositosAprovados,
                    'depositos_liquidos' => (float) $depositosLiquidos,
                    'pix_gerados' => $pixGerados,
                    'lucro_plataforma' => (float) $lucroPlataforma,
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas: ' . $th->getMessage(),
            ], 500);
        }
    }
}

