<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FullPixSale;
use App\Models\LiberpaySale;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class IndexDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $user = Auth::user();
        
        // Calcular período do ciclo atual (15 dias)
        $cyclePeriod = $this->getCurrent15DayCycle();
        $startDate = $cyclePeriod['start'];
        $endDate = $cyclePeriod['end'];

        // Saldo Disponível (balance do usuário)
        $saldoDisponivel = $user->balance ?? 0;

        // Faturamento (valor bruto - total_amount) - TODAS as transações, independente da data
        $faturamento = Transaction::where('user_id', $user->id)
            ->where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->sum('total_amount');

        // Quantidade de vendas - TODAS as transações, independente da data
        $quantidade = Transaction::where('user_id', $user->id)
            ->where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->count();

        // Ticket Médio (faturamento / quantidade) - cálculo preciso
        $ticketMedio = $quantidade > 0 ? ($faturamento / $quantidade) : 0;

        // Dados do ciclo atual para gráfico (15 dias)
        $chartData = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            // Usar total_amount (faturamento bruto) ao invés de net_deposit
            // Comparar diretamente com o campo date usando whereDate para garantir precisão
            $dateString = $currentDate->format('Y-m-d');
            $faturamentoDia = Transaction::where('user_id', $user->id)
                ->whereDate('date', $dateString)
                ->where('payment_status', 'Paid')
                ->where('is_sample', false)
                ->sum('total_amount');

            $chartData[] = [
                'date' => $currentDate->format('d/m'),
                'Faturamento' => (float) $faturamentoDia,
            ];
            
            $currentDate->addDay();
        }

        // Métodos de Pagamento (transações do período do ciclo atual)
        // Usar whereDate para garantir que inclui a data final corretamente
        $transactions = Transaction::where('user_id', $user->id)
            ->where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->whereDate('date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('date', '<=', $endDate->format('Y-m-d'))
            ->get();

        $totalTransactions = $transactions->count();
        
        // Contar métodos de pagamento (case-insensitive)
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

        // Resumo Financeiro - Conversão PIX (baseada em quantidade) - apenas do período do ciclo atual
        // Contar PIX gerados (LiberpaySale + FullPixSale) - considerar apenas do período
        $pixGeradosLiberpay = LiberpaySale::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->count();
        $pixGeradosFullpix = FullPixSale::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->count();
        $pixGerados = $pixGeradosLiberpay + $pixGeradosFullpix;
        
        // Contar PIX pagos (Transaction com payment_status = 'Paid' e payment_method contém 'pix')
        // Usar whereDate para garantir que inclui a data final corretamente
        $pixPagos = Transaction::where('user_id', $user->id)
            ->where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->whereDate('date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('date', '<=', $endDate->format('Y-m-d'))
            ->where(function ($query) {
                $query->where('payment_method', 'like', '%pix%')
                    ->orWhere('payment_method', 'like', '%PIX%');
            })
            ->count();
        
        // Calcular conversão: (pixPagos / pixGerados) * 100
        $conversao = $pixGerados > 0 ? round(($pixPagos / $pixGerados) * 100, 2) : 0;

        $reembolsos = Transaction::where('user_id', $user->id)
            ->where('payment_status', 'Unpaid')
            ->where('is_sample', false)
            ->whereDate('date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('date', '<=', $endDate->format('Y-m-d'))
            ->count();

        // Transações recentes (últimas 10) - com eager loading para evitar lazy loading
        $transacoesRecentes = Transaction::with('user')
            ->where('user_id', $user->id)
            ->where('is_sample', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'cliente' => $transaction->user->name ?? 'N/A',
                    'valor' => number_format((float) $transaction->net_deposit, 2, ',', '.'),
                    'tipo' => $transaction->payment_status === 'Paid' ? 'entrada' : 'saida',
                    'data' => $transaction->created_at->format('Y-m-d H:i'),
                    'metodo' => $transaction->payment_method,
                ];
            });

        // Buscar banner promocional
        $bannerUrl = $this->getBannerUrl();

        return Inertia::render('Dashboard', [
            'stats' => [
                'saldoDisponivel' => (float) $saldoDisponivel,
                'faturamento' => (float) $faturamento,
                'quantidade' => $quantidade,
                'ticketMedio' => (float) $ticketMedio,
            ],
            'chartData' => $chartData,
            'paymentMethods' => $paymentMethods,
            'financialSummary' => [
                'conversao' => $conversao,
                'reembolsos' => $reembolsos,
                'preChargeback' => 0, // TODO: implementar quando houver tabela de chargebacks
                'chargeback' => 0, // TODO: implementar quando houver tabela de chargebacks
            ],
            'transacoesRecentes' => $transacoesRecentes,
            'bannerUrl' => $bannerUrl,
        ]);
    }

    /**
     * Calcula o período do ciclo atual de 15 dias
     * Ciclo 1: dia 28 ao dia 11 (do mês seguinte)
     * Ciclo 2: dia 12 ao dia 26
     * Ciclo 3: dia 27 ao dia 10 (do mês seguinte)
     */
    private function getCurrent15DayCycle(): array
    {
        $today = now();
        $day = $today->day;
        $month = $today->month;
        $year = $today->year;

        // Determinar qual ciclo estamos
        // Ciclo 1: 28 ao 11 (cruza meses)
        // Ciclo 2: 12 ao 26 (mesmo mês)
        // Ciclo 3: 27 ao 10 (do mês seguinte)
        
        if ($day >= 28) {
            // Estamos no início do Ciclo 1 (dia 28-31 do mês atual)
            $startDate = \Carbon\Carbon::create($year, $month, 28)->startOfDay();
            $endDate = \Carbon\Carbon::create($year, $month, 11)->addMonth()->startOfDay();
        } elseif ($day >= 1 && $day <= 10) {
            // Estamos no final do Ciclo 3 (dias 1-10 do mês atual)
            // O ciclo 3 começou no dia 27 do mês anterior
            $startDate = \Carbon\Carbon::create($year, $month, 27)->subMonth()->startOfDay();
            $endDate = \Carbon\Carbon::create($year, $month, 10)->startOfDay();
        } elseif ($day == 11) {
            // Estamos no final do Ciclo 1 (dia 11 do mês atual)
            // O ciclo 1 começou no dia 28 do mês anterior
            $startDate = \Carbon\Carbon::create($year, $month, 28)->subMonth()->startOfDay();
            $endDate = \Carbon\Carbon::create($year, $month, 11)->startOfDay();
        } elseif ($day >= 12 && $day <= 26) {
            // Ciclo 2: 12 ao 26 (mesmo mês)
            $startDate = \Carbon\Carbon::create($year, $month, 12)->startOfDay();
            $endDate = \Carbon\Carbon::create($year, $month, 26)->startOfDay();
        } else {
            // Ciclo 3: 27 ao 10 (do mês seguinte)
            // Se hoje é dia 27, o ciclo começou hoje
            $startDate = \Carbon\Carbon::create($year, $month, 27)->startOfDay();
            $endDate = \Carbon\Carbon::create($year, $month, 10)->addMonth()->startOfDay();
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
        ];
    }

    /**
     * Buscar URL do banner promocional
     */
    private function getBannerUrl(): ?string
    {
        $bannerPath = null;
        
        // Tentar buscar da tabela settings
        if (DB::getSchemaBuilder()->hasTable('settings')) {
            $setting = DB::table('settings')->where('key', 'banner_promocional')->first();
            $bannerPath = $setting?->value;
        }
        
        // Se não encontrou, tentar system_images
        if (!$bannerPath && DB::getSchemaBuilder()->hasTable('system_images')) {
            $image = DB::table('system_images')->where('key', 'banner_promocional')->first();
            $bannerPath = $image?->value;
        }
        
        // Se encontrou um caminho, retornar URL pública
        if ($bannerPath) {
            return Storage::url($bannerPath);
        }
        
        return null;
    }
}

