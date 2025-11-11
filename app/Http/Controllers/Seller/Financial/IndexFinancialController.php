<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Financial;

use App\Http\Controllers\Controller;
use App\Models\Acquirer;
use App\Models\PixKey;
use App\Models\SystemSetting;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class IndexFinancialController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $user = Auth::user();

        // Buscar transações pagas (entradas)
        $transacoesPagas = Transaction::where('user_id', $user->id)
            ->where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'descricao' => "Venda #{$transaction->invoice}",
                    'tipo' => 'Pagamento',
                    'entrada' => (float) $transaction->net_deposit,
                    'saida' => null,
                    'data' => $transaction->created_at->format('d \d\e M\. H:i'),
                ];
            });
        
        // Buscar saques (se houver tabela de withdrawals)
        $saques = collect();
        $withdrawalsPaginated = null;
        if (Schema::hasTable('withdrawals')) {
            $withdrawalsQuery = DB::table('withdrawals')
                ->where('user_id', $user->id)
                ->where('is_sample', false)
                ->orderBy('created_at', 'desc');
            
            // Paginar withdrawals
            $withdrawalsPage = $request->get('withdrawals_page', 1);
            $withdrawalsPerPage = 10;
            $withdrawalsTotal = $withdrawalsQuery->count();
            $withdrawalsItems = $withdrawalsQuery
                ->skip(($withdrawalsPage - 1) * $withdrawalsPerPage)
                ->take($withdrawalsPerPage)
                ->get()
                ->map(function ($withdrawal) {
                    return [
                        'id' => $withdrawal->id,
                        'amount' => (float) $withdrawal->amount,
                        'fee' => (float) ($withdrawal->fee ?? 0),
                        'net_amount' => (float) ($withdrawal->net_amount ?? 0),
                        'pix_key' => $withdrawal->pix_key,
                        'pix_type' => $withdrawal->pix_type,
                        'pix_key_description' => $withdrawal->pix_key_description ?? '',
                        'status' => $this->mapWithdrawalStatus($withdrawal->status ?? 'pending'),
                        'created_at' => $withdrawal->created_at,
                        'paid_at' => $withdrawal->paid_at,
                    ];
                });

            // Criar paginação para withdrawals
            $withdrawalsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
                $withdrawalsItems,
                $withdrawalsTotal,
                $withdrawalsPerPage,
                $withdrawalsPage,
                [
                    'path' => $request->url(),
                    'query' => array_merge($request->query(), ['withdrawals_page' => $withdrawalsPage]),
                    'pageName' => 'withdrawals_page'
                ]
            );
            $withdrawalsPaginated->withPath($request->url());

            // Para o extrato, usar os itens paginados convertidos para array
            $saques = $withdrawalsItems->map(function ($withdrawal) {
                return [
                    'id' => 'withdrawal-' . $withdrawal['id'],
                    'descricao' => "Saque #{$withdrawal['id']}",
                    'tipo' => 'Saque',
                    'entrada' => null,
                    'saida' => in_array($withdrawal['status'], ['Aprovado', 'Finalizado']) ? $withdrawal['amount'] : null,
                    'data' => \Carbon\Carbon::parse($withdrawal['created_at'])->format('d \d\e M\. H:i'),
                    'status' => $withdrawal['status'],
                ];
            });
        }
        
        if (!$withdrawalsPaginated) {
            $withdrawalsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                10,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }
        
        // Combinar e ordenar
        $extratoData = $transacoesPagas->concat($saques)
            ->sortByDesc(function ($item) {
                return $item['data'];
            })
            ->values();
        
        // Paginar extrato
        $page = $request->get('page', 1);
        $perPage = 10;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $extratoData->forPage($page, $perPage),
            $extratoData->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Saldos reais
        $saldoDisponivel = $user->balance ?? 0;
        $saldoPendente = Transaction::where('user_id', $user->id)
            ->where('payment_status', 'Pending')
            ->where('is_sample', false)
            ->sum('net_deposit');
        $aguardandoAntecipacao = 0; // TODO: implementar quando houver tabela de antecipações
        $reservaFinanceira = 0; // TODO: implementar quando houver configuração de reserva

        // Buscar adquirente ativa e taxas
        $activeAcquirer = Acquirer::where('is_active', true)->first();
        $acquirerFixedFee = $activeAcquirer ? (float) ($activeAcquirer->fixed_fee ?? 0.00) : 0.00;
        $acquirerPercentageFee = $activeAcquirer ? (float) ($activeAcquirer->percentage_fee ?? 0.00) : 0.00;
        // Taxa de saque da adquirente (usar withdrawal_fee se disponível, senão fixed_fee)
        $acquirerWithdrawalFee = $activeAcquirer ? (float) ($activeAcquirer->withdrawal_fee ?? $activeAcquirer->fixed_fee ?? 0.00) : 0.00;
        
        // Buscar taxas da LuckPay (sem valor padrão, usar 0 se não configurado)
        $gatewayPixFixedSetting = SystemSetting::where('key', 'gateway_pix_fixed')->first();
        $gatewayPixFixed = $gatewayPixFixedSetting ? (float) $gatewayPixFixedSetting->value : 0.00;
        
        $gatewayPixPercentageSetting = SystemSetting::where('key', 'gateway_pix_percentage')->first();
        $gatewayPixPercentage = $gatewayPixPercentageSetting ? (float) $gatewayPixPercentageSetting->value : 0.00;

        // Buscar configurações de saque
        $minWithdraw = (float) SystemSetting::get('min_withdraw', 10.00);
        $fixedWithdrawFee = (float) SystemSetting::get('fixed_withdraw_fee', 5.00);
        $percentWithdrawFee = (float) SystemSetting::get('percent_withdraw_fee', 0);

        // Buscar chaves PIX do usuário
        $pixKeys = PixKey::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Seller/Financial/Index', [
            'extratoData' => $paginated,
            'saldoDisponivel' => (float) $saldoDisponivel,
            'saldoPendente' => (float) $saldoPendente,
            'aguardandoAntecipacao' => (float) $aguardandoAntecipacao,
            'reservaFinanceira' => (float) $reservaFinanceira,
            'acquirerFixedFee' => $acquirerFixedFee,
            'acquirerPercentageFee' => $acquirerPercentageFee,
            'acquirerWithdrawalFee' => $acquirerWithdrawalFee,
            'gatewayPixFixed' => $gatewayPixFixed,
            'gatewayPixPercentage' => $gatewayPixPercentage,
            'minWithdraw' => $minWithdraw,
            'fixedWithdrawFee' => $fixedWithdrawFee,
            'percentWithdrawFee' => $percentWithdrawFee,
            'pixKeys' => $pixKeys,
            'withdrawals' => $withdrawalsPaginated,
        ]);
    }

    private function mapWithdrawalStatus(string $status): string
    {
        return match(strtolower($status)) {
            'pending' => 'Pendente',
            'approved', 'aprovado' => 'Aprovado',
            'processing' => 'Processando',
            'done', 'done_manual' => 'Finalizado',
            'failed' => 'Falhou',
            'refused' => 'Recusado',
            'cancelled', 'cancelado' => 'Cancelado',
            default => 'Pendente',
        };
    }
}

