<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FullPixSale;
use App\Models\LiberpaySale;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellerStatsController extends Controller
{
    /**
     * Retorna estatísticas do seller
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Saldo disponível
        $saldoDisponivel = $user->balance ?? 0;

        // Faturamento (valor bruto - total_amount)
        $faturamento = Transaction::where('user_id', $user->id)
            ->where('is_sample', false)
            ->where('payment_status', 'Paid')
            ->sum('total_amount') ?? 0;

        // Quantidade de vendas
        $quantidade = Transaction::where('user_id', $user->id)
            ->where('is_sample', false)
            ->where('payment_status', 'Paid')
            ->count();

        // Ticket médio (faturamento / quantidade) - cálculo preciso
        $ticketMedio = $quantidade > 0 ? ($faturamento / $quantidade) : 0;

        // Métodos de pagamento (%)
        $transactions = Transaction::where('user_id', $user->id)
            ->where('is_sample', false)
            ->where('payment_status', 'Paid')
            ->get();

        $totalTransactions = $transactions->count();
        
        $pixCount = $transactions->filter(function ($t) {
            return stripos($t->payment_method, 'pix') !== false;
        })->count();
        
        $cardCount = $transactions->filter(function ($t) {
            return stripos($t->payment_method, 'cartão') !== false || 
                   stripos($t->payment_method, 'card') !== false ||
                   stripos($t->payment_method, 'credito') !== false ||
                   stripos($t->payment_method, 'crédito') !== false;
        })->count();
        
        $boletoCount = $transactions->filter(function ($t) {
            return stripos($t->payment_method, 'boleto') !== false;
        })->count();

        $paymentMethods = [
            'pix' => $totalTransactions > 0 ? round(($pixCount / $totalTransactions) * 100) : 0,
            'card' => $totalTransactions > 0 ? round(($cardCount / $totalTransactions) * 100) : 0,
            'boleto' => $totalTransactions > 0 ? round(($boletoCount / $totalTransactions) * 100) : 0,
        ];

        // Resumo Financeiro - Conversão PIX (baseada em quantidade)
        // Contar PIX gerados (LiberpaySale + FullPixSale)
        $pixGeradosLiberpay = LiberpaySale::where('user_id', $user->id)->count();
        $pixGeradosFullpix = FullPixSale::where('user_id', $user->id)->count();
        $pixGerados = $pixGeradosLiberpay + $pixGeradosFullpix;
        
        // Contar PIX pagos (Transaction com payment_status = 'Paid' e payment_method contém 'pix')
        $pixPagos = Transaction::where('user_id', $user->id)
            ->where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->where(function ($query) {
                $query->where('payment_method', 'like', '%pix%')
                    ->orWhere('payment_method', 'like', '%PIX%');
            })
            ->count();
        
        // Calcular conversão: (pixPagos / pixGerados) * 100
        $conversao = $pixGerados > 0 ? round(($pixPagos / $pixGerados) * 100, 2) : 0;

        $reembolsos = Transaction::where('user_id', $user->id)
            ->where('is_sample', false)
            ->where('payment_status', 'Unpaid')
            ->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'saldoDisponivel' => (float) $saldoDisponivel,
                'faturamento' => (float) $faturamento,
                'quantidade' => $quantidade,
                'ticketMedio' => (float) $ticketMedio,
                'paymentMethods' => $paymentMethods,
                'conversao' => $conversao,
                'reembolsos' => $reembolsos,
                'preChargeback' => 0, // TODO: implementar quando houver tabela
                'chargeback' => 0, // TODO: implementar quando houver tabela
            ],
        ]);
    }
}

