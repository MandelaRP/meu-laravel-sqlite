<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionCountController extends Controller
{
    /**
     * Retorna contagem de transações
     */
    public function __invoke(Request $request): JsonResponse
    {
        $total = Transaction::where('is_sample', false)->count();
        $paid = Transaction::where('is_sample', false)->where('payment_status', 'Paid')->count();
        $pending = Transaction::where('is_sample', false)->where('payment_status', 'Pending')->count();
        $unpaid = Transaction::where('is_sample', false)->where('payment_status', 'Unpaid')->count();

        return response()->json([
            'success' => true,
            'count' => [
                'total' => $total,
                'paid' => $paid,
                'pending' => $pending,
                'unpaid' => $unpaid,
            ],
        ]);
    }
}

