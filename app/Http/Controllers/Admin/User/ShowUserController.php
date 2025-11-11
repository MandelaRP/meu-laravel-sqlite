<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\FeeCalculationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShowUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, User $user)
    {
        // Carregar relacionamentos antes de usar para evitar lazy loading
        $user->load(['addresses', 'acquirer']);
        
        // Buscar todas as adquirentes para o seletor
        $acquirers = \App\Models\Acquirer::orderBy('name')->get();

        // Buscar adquirente ativa (global)
        $activeAcquirer = \App\Models\Acquirer::where('is_active', true)->first();
        
        // Buscar taxas globais do sistema (Cash In = Taxas PIX e Gateway)
        $gatewayPixPercentageSetting = SystemSetting::where('key', 'gateway_pix_percentage')->first();
        $gatewayPixFixedSetting = SystemSetting::where('key', 'gateway_pix_fixed')->first();
        $globalCashInPercentage = $gatewayPixPercentageSetting ? (float) $gatewayPixPercentageSetting->value : 0;
        $globalCashInFixed = $gatewayPixFixedSetting ? (float) $gatewayPixFixedSetting->value : 0;
        
        // Buscar taxas globais do sistema (Cash Out = Taxas de Saque)
        $percentWithdrawFeeSetting = SystemSetting::where('key', 'percent_withdraw_fee')->first();
        $fixedWithdrawFeeSetting = SystemSetting::where('key', 'fixed_withdraw_fee')->first();
        $globalCashOutPercentage = $percentWithdrawFeeSetting ? (float) $percentWithdrawFeeSetting->value : 0;
        $globalCashOutFixed = $fixedWithdrawFeeSetting ? (float) $fixedWithdrawFeeSetting->value : 0;
        
        // Determinar qual adquirente o usuário está usando
        $userAcquirer = null;
        if ($user->preferred_acquirer) {
            $userAcquirer = \App\Models\Acquirer::where('slug', $user->preferred_acquirer)->first();
        }
        $currentAcquirer = $userAcquirer ?? $activeAcquirer;

        // Buscar estatísticas do usuário (sem lazy loading)
        $stats = $this->getUserStats($user);
        
        // Buscar transações recentes (pendentes e pagas)
        $recentTransactions = $this->getRecentTransactions($user);

        return Inertia::render('Admin/User/Show', [
            'user' => $user,
            'stats' => $stats,
            'acquirers' => $acquirers,
            'recentTransactions' => $recentTransactions,
            'globalTaxes' => [
                'cash_in_percentage' => (float) $globalCashInPercentage,
                'cash_in_fixed' => (float) $globalCashInFixed,
                'cash_out_percentage' => (float) $globalCashOutPercentage,
                'cash_out_fixed' => (float) $globalCashOutFixed,
            ],
            'currentAcquirer' => $currentAcquirer ? [
                'id' => $currentAcquirer->id,
                'name' => $currentAcquirer->name,
                'slug' => $currentAcquirer->slug,
            ] : null,
        ]);
    }

    private function getUserStats(User $user): array
    {
        // Buscar transações sem lazy loading
        // Incluir transações do admin12@gmail.com mesmo se tiverem is_sample = true
        $transactions = \App\Models\Transaction::where('user_id', $user->id)
            ->where(function ($q) use ($user) {
                $q->where('is_sample', false);
                // Se for o admin12@gmail.com, incluir todas as transações
                if ($user->email === 'admin12@gmail.com') {
                    $q->orWhere('is_sample', true);
                }
            })
            ->get();

        $paidTransactions = $transactions->where('payment_status', 'Paid');
        
        // Calcular taxas de aprovação
        $totalTransacoes = $transactions->count();
        $transacoesAprovadas = $paidTransactions->count();
        $taxaAprovacao = $totalTransacoes > 0 ? ($transacoesAprovadas / $totalTransacoes) * 100 : 0;
        
        // Calcular variação mensal de depósitos (comparar mês atual com anterior)
        $mesAtual = now()->startOfMonth();
        $mesAnterior = now()->subMonth()->startOfMonth();
        
        $depositosMesAtual = $paidTransactions->filter(function ($t) use ($mesAtual) {
            return $t->date && $t->date->isSameMonth($mesAtual);
        })->sum('total_amount');
        
        $depositosMesAnterior = $paidTransactions->filter(function ($t) use ($mesAnterior) {
            return $t->date && $t->date->isSameMonth($mesAnterior);
        })->sum('total_amount');
        
        $variacaoMensal = $depositosMesAnterior > 0 
            ? (($depositosMesAtual - $depositosMesAnterior) / $depositosMesAnterior) * 100 
            : ($depositosMesAtual > 0 ? 100 : 0);
        
        // Contar PIX gerados (incluindo pendentes)
        $pixGerados = \App\Models\LiberpaySale::where('user_id', $user->id)->count();

        return [
            'saldo_atual' => (float) ($user->balance ?? 0),
            'volume_transacionado' => (float) $paidTransactions->sum(fn($t) => (float) $t->total_amount),
            'transacoes_total' => $totalTransacoes,
            'transacoes_aprovadas' => $transacoesAprovadas,
            'taxa_aprovacao' => round($taxaAprovacao, 2),
            'depositos_aprovados' => (float) $paidTransactions->sum(fn($t) => (float) $t->total_amount),
            'variacao_mensal' => round($variacaoMensal, 2),
            'depositos_liquidos' => (float) $paidTransactions->sum(fn($t) => (float) $t->net_deposit),
            'pix_gerados' => $pixGerados,
            'lucro_plataforma' => (float) $paidTransactions->sum(fn($t) => (float) $t->fee),
        ];
    }
    
    private function getRecentTransactions(User $user): array
    {
        $transactions = collect();
        
        // Buscar transações pagas
        // Incluir transações do admin12@gmail.com mesmo se tiverem is_sample = true
        $paidTransactions = \App\Models\Transaction::where('user_id', $user->id)
            ->where(function ($q) use ($user) {
                $q->where('is_sample', false);
                // Se for o admin12@gmail.com, incluir todas as transações
                if ($user->email === 'admin12@gmail.com') {
                    $q->orWhere('is_sample', true);
                }
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        foreach ($paidTransactions as $transaction) {
            $liberpaySale = \App\Models\LiberpaySale::where('liberpay_sale_id', $transaction->acquirer_ref)->first();
            
            $transactions->push([
                'id' => $transaction->id,
                'transaction_id' => $transaction->acquirer_ref ?? $transaction->invoice,
                'status' => 'Pago',
                'raw_status' => 'paid',
                'payment_method' => $transaction->payment_method ?? 'PIX',
                'total_amount' => (float) $transaction->total_amount,
                'fee' => (float) $transaction->fee,
                'date' => $transaction->date?->format('Y-m-d H:i:s') ?? $transaction->created_at->format('Y-m-d H:i:s'),
                'product' => $liberpaySale?->liberpay_response['items'][0]['title'] ?? 'Depósito',
            ]);
        }
        
        // Buscar vendas pendentes
        $pendingSales = \App\Models\LiberpaySale::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereDoesntHave('transaction')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        foreach ($pendingSales as $sale) {
            $acquirer = \App\Models\Acquirer::where('slug', 'liberpay')->first();
            $gatewayFeePercentage = $acquirer?->gateway_fee_percentage ?? 2.99;
            $fee = ($sale->amount * $gatewayFeePercentage) / 100;
            
            $transactions->push([
                'id' => 'pending-' . $sale->id,
                'transaction_id' => $sale->liberpay_sale_id,
                'status' => 'Pendente',
                'raw_status' => 'pending',
                'payment_method' => 'PIX',
                'total_amount' => (float) $sale->amount,
                'fee' => (float) $fee,
                'date' => $sale->created_at->format('Y-m-d H:i:s'),
                'product' => $sale->liberpay_response['items'][0]['title'] ?? 'Depósito',
            ]);
        }
        
        // Ordenar por data (mais recente primeiro)
        return $transactions->sortByDesc('date')->values()->take(20)->all();
    }
}
