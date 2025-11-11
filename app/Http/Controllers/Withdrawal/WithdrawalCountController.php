<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Withdrawal;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalCountController extends Controller
{
    /**
     * Retorna contagem de saques
     */
    public function __invoke(Request $request): JsonResponse
    {
        $hasWithdrawalsTable = DB::getSchemaBuilder()->hasTable('withdrawals');
        
        if ($hasWithdrawalsTable) {
            $total = DB::table('withdrawals')->where('is_sample', false)->count();
            $approved = DB::table('withdrawals')->where('is_sample', false)->where('status', 'aprovado')->count();
            $pending = DB::table('withdrawals')->where('is_sample', false)->where('status', 'pendente')->count();
            $cancelled = DB::table('withdrawals')->where('is_sample', false)->where('status', 'cancelado')->count();
        } else {
            // Fallback: usar transações
            $total = DB::table('transactions')
                ->where('transactions.is_sample', false)
                ->where('transactions.payment_method', 'like', '%saque%')
                ->count();
            $approved = DB::table('transactions')
                ->where('transactions.is_sample', false)
                ->where('transactions.payment_method', 'like', '%saque%')
                ->where('transactions.payment_status', 'Paid')
                ->count();
            $pending = DB::table('transactions')
                ->where('transactions.is_sample', false)
                ->where('transactions.payment_method', 'like', '%saque%')
                ->where('transactions.payment_status', 'Pending')
                ->count();
            $cancelled = DB::table('transactions')
                ->where('transactions.is_sample', false)
                ->where('transactions.payment_method', 'like', '%saque%')
                ->where('transactions.payment_status', 'Unpaid')
                ->count();
        }

        return response()->json([
            'success' => true,
            'count' => [
                'total' => $total,
                'approved' => $approved,
                'pending' => $pending,
                'cancelled' => $cancelled,
            ],
        ]);
    }
}

