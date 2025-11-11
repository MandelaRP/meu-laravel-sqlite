<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FeeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class IndexAdminDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $sevenDaysAgo = now()->subDays(7)->startOfDay();

        // Lucro líquido da Luckpay (apenas taxa da gateway, não inclui taxa da adquirente)
        // Usar BC Math para soma precisa
        $lucroLiquidoHoje = Transaction::whereDate('date', $today)
            ->where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->get()
            ->reduce(function($carry, $t) {
                // Lucro = taxa da gateway = fee (que já contém apenas a taxa da gateway)
                $fee = number_format((float) $t->fee, 2, '.', '');
                return bcadd($carry ?? '0', $fee, 2);
            }, '0');
        $lucroLiquidoHoje = (float) $lucroLiquidoHoje;
        
        // Adicionar lucro líquido dos saques de hoje
        // Lucro dos saques = gateway_fee (taxa da gateway, lucro líquido da plataforma)
        if (Schema::hasTable('withdrawals')) {
            $withdrawalsProfitToday = DB::table('withdrawals')
                ->whereDate('created_at', $today)
                ->whereIn('status', ['approved', 'done', 'processing'])
                ->where('is_sample', false)
                ->sum('gateway_fee') ?? 0;
            
            // Adicionar ao lucro líquido total
            $lucroLiquidoHoje = bcadd((string) $lucroLiquidoHoje, number_format((float) $withdrawalsProfitToday, 2, '.', ''), 2);
            $lucroLiquidoHoje = (float) $lucroLiquidoHoje;
        }

        $transacaoTotalHoje = Transaction::whereDate('date', $today)
            ->where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->sum('total_amount');

        // Buscar saques de hoje (valor bruto, sem taxas)
        $saquesHoje = 0;
        if (Schema::hasTable('withdrawals')) {
            $saquesHoje = DB::table('withdrawals')
                ->whereDate('created_at', $today)
                ->whereIn('status', ['approved', 'done', 'processing'])
                ->where('is_sample', false)
                ->sum('amount') ?? 0;
        } else {
            // Fallback: usar transações
            $saquesHoje = Transaction::whereDate('date', $today)
                ->where('payment_method', 'like', '%saque%')
                ->where('payment_status', 'Paid')
                ->where('is_sample', false)
                ->sum('total_amount');
        }

        // Lucro líquido da Luckpay (apenas taxa da gateway)
        // Usar BC Math para soma precisa
        $lucroLiquidoDiaAnterior = Transaction::whereDate('date', $yesterday)
            ->where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->get()
            ->reduce(function($carry, $t) {
                // Lucro = taxa da gateway = fee
                $fee = number_format((float) $t->fee, 2, '.', '');
                return bcadd($carry ?? '0', $fee, 2);
            }, '0');
        $lucroLiquidoDiaAnterior = (float) $lucroLiquidoDiaAnterior;
        
        // Adicionar lucro líquido dos saques de ontem
        if (Schema::hasTable('withdrawals')) {
            $withdrawalsProfitYesterday = DB::table('withdrawals')
                ->whereDate('created_at', $yesterday)
                ->whereIn('status', ['approved', 'done', 'processing'])
                ->where('is_sample', false)
                ->sum('gateway_fee') ?? 0;
            
            // Adicionar ao lucro líquido total
            $lucroLiquidoDiaAnterior = bcadd((string) $lucroLiquidoDiaAnterior, number_format((float) $withdrawalsProfitYesterday, 2, '.', ''), 2);
            $lucroLiquidoDiaAnterior = (float) $lucroLiquidoDiaAnterior;
        }

        $transacaoTotalDiaAnterior = Transaction::whereDate('date', $yesterday)
            ->where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->sum('total_amount');

        // Buscar saques de ontem (valor bruto, sem taxas)
        $saquesDiaAnterior = 0;
        if (Schema::hasTable('withdrawals')) {
            $saquesDiaAnterior = DB::table('withdrawals')
                ->whereDate('created_at', $yesterday)
                ->whereIn('status', ['approved', 'done', 'processing'])
                ->where('is_sample', false)
                ->sum('amount') ?? 0;
        } else {
            // Fallback: usar transações
            $saquesDiaAnterior = Transaction::whereDate('date', $yesterday)
                ->where('payment_method', 'like', '%saque%')
                ->where('payment_status', 'Paid')
                ->where('is_sample', false)
                ->sum('total_amount');
        }

        // Total de usuários - excluir is_sample e admins, mas incluir admin12@gmail.com
        // Excluir usuários recent_user (que não finalizaram o onboarding)
        $totalUsuarios = User::where('role', '!=', 'admin')
            ->where('status', '!=', 'recent_user')
            ->where(function ($q) {
                $q->where('is_sample', false)
                  ->orWhere('email', 'admin12@gmail.com');
            })
            ->count();
        
        // Usuários pendentes (para badge na sidebar) - excluir is_sample, mas incluir admin12@gmail.com
        // Excluir usuários recent_user (já está implícito pois status = 'pending')
        $pendingUsers = User::where('status', 'pending')
            ->where('role', '!=', 'admin')
            ->where(function ($q) {
                $q->where('is_sample', false)
                  ->orWhere('email', 'admin12@gmail.com');
            })
            ->count();

        // Valor transacionado total (Faturamento - valor bruto) - excluir is_sample
        $faturamento = Transaction::where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->sum('total_amount');
        
        // Quantidade de vendas aprovadas
        $quantidadeVendas = Transaction::where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->count();
        
        // Ticket Médio
        $ticketMedio = $quantidadeVendas > 0 ? ($faturamento / $quantidadeVendas) : 0;
        
        // Saldo Disponível (soma de todos os net_deposit)
        $saldoDisponivel = Transaction::where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->get()
            ->sum(fn($t) => (float) $t->net_deposit);
        
        // Métodos de pagamento (percentuais)
        $paymentMethods = $this->calculatePaymentMethods();
        
        // Taxa da adquirente ativa
        $acquirerFee = FeeCalculationService::getActiveAcquirerFixedFee();

        // Calcular período do ciclo atual de 7 dias
        $cyclePeriod = $this->getCurrent7DayCycle();
        $startDate = $cyclePeriod['start'];
        $endDate = $cyclePeriod['end'];

        // Dados do ciclo atual para gráficos (7 dias)
        $chartData = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            // Lucro líquido da Luckpay (apenas taxa da gateway)
            // Usar BC Math para soma precisa
            // Usar whereDate para garantir comparação precisa da data
            $dateString = $currentDate->format('Y-m-d');
            $lucroLiquido = Transaction::whereDate('date', $dateString)
                ->where('payment_status', 'Paid')
                ->where('is_sample', false)
                ->get()
                ->reduce(function($carry, $t) {
                    // Lucro = taxa da gateway = fee
                    $fee = number_format((float) $t->fee, 2, '.', '');
                    return bcadd($carry ?? '0', $fee, 2);
                }, '0');
            $lucroLiquido = (float) $lucroLiquido;
            
            // Adicionar lucro líquido dos saques deste dia
            // Lucro dos saques = gateway_fee (taxa da gateway, lucro líquido da plataforma)
            if (Schema::hasTable('withdrawals')) {
                $withdrawalsProfitDay = DB::table('withdrawals')
                    ->whereDate('created_at', $dateString)
                    ->whereIn('status', ['approved', 'done', 'processing'])
                    ->where('is_sample', false)
                    ->sum('gateway_fee') ?? 0;
                
                // Adicionar ao lucro líquido total usando BC Math
                $lucroLiquido = bcadd((string) $lucroLiquido, number_format((float) $withdrawalsProfitDay, 2, '.', ''), 2);
                $lucroLiquido = (float) $lucroLiquido;
            }

            // Usar whereDate para garantir comparação precisa da data
            $transacaoTotal = Transaction::whereDate('date', $dateString)
                ->where('payment_status', 'Paid')
                ->where('is_sample', false)
                ->sum('total_amount');

            $chartData[] = [
                'date' => $currentDate->format('d/m'),
                'lucro_liquido' => (float) $lucroLiquido,
                'transacao_total' => (float) $transacaoTotal,
            ];
            
            $currentDate->addDay();
        }

        return Inertia::render('Admin/Dashboard/Index', [
            'financialData' => [
                'lucroLiquidoHoje' => (float) $lucroLiquidoHoje,
                'transacaoTotalHoje' => (float) $transacaoTotalHoje,
                'saquesHoje' => (float) $saquesHoje,
                'totalUsuarios' => $totalUsuarios,
                'lucroLiquidoDiaAnterior' => (float) $lucroLiquidoDiaAnterior,
                'transacaoTotalDiaAnterior' => (float) $transacaoTotalDiaAnterior,
                'saquesDiaAnterior' => (float) $saquesDiaAnterior,
                'valorTransacionadoTotal' => (float) $faturamento,
                'saldoDisponivel' => (float) $saldoDisponivel,
                'faturamento' => (float) $faturamento,
                'quantidadeVendas' => $quantidadeVendas,
                'ticketMedio' => (float) $ticketMedio,
                'paymentMethods' => $paymentMethods,
                'acquirerFee' => (float) $acquirerFee,
            ],
            'chartData' => $chartData,
            'pendingUsers' => $pendingUsers,
            'paymentMethodsStatus' => [
                'pix' => SystemSetting::get('payment_method_pix', true),
                'credit_card' => SystemSetting::get('payment_method_credit_card', true),
                'boleto' => SystemSetting::get('payment_method_boleto', true),
            ],
        ]);
    }
    
    /**
     * Calcula o período do ciclo atual de 7 dias
     * Ciclos semanais que se repetem a cada 7 dias:
     * - Ciclo 1: dias 1-7
     * - Ciclo 2: dias 8-14
     * - Ciclo 3: dias 15-21
     * - Ciclo 4: dias 22-28
     * - Ciclo 5: dias 29-31 (ou até o final do mês)
     */
    private function getCurrent7DayCycle(): array
    {
        $today = now();
        $day = $today->day;
        $month = $today->month;
        $year = $today->year;

        // Determinar qual ciclo de 7 dias estamos
        if ($day >= 1 && $day <= 7) {
            // Ciclo 1: dias 1-7
            $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
            $endDate = \Carbon\Carbon::create($year, $month, 7)->startOfDay();
        } elseif ($day >= 8 && $day <= 14) {
            // Ciclo 2: dias 8-14
            $startDate = \Carbon\Carbon::create($year, $month, 8)->startOfDay();
            $endDate = \Carbon\Carbon::create($year, $month, 14)->startOfDay();
        } elseif ($day >= 15 && $day <= 21) {
            // Ciclo 3: dias 15-21
            $startDate = \Carbon\Carbon::create($year, $month, 15)->startOfDay();
            $endDate = \Carbon\Carbon::create($year, $month, 21)->startOfDay();
        } elseif ($day >= 22 && $day <= 28) {
            // Ciclo 4: dias 22-28
            $startDate = \Carbon\Carbon::create($year, $month, 22)->startOfDay();
            $endDate = \Carbon\Carbon::create($year, $month, 28)->startOfDay();
        } else {
            // Ciclo 5: dias 29-31 (ou até o final do mês)
            $lastDayOfMonth = $today->copy()->endOfMonth()->day;
            $startDate = \Carbon\Carbon::create($year, $month, 29)->startOfDay();
            $endDate = \Carbon\Carbon::create($year, $month, $lastDayOfMonth)->startOfDay();
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
        ];
    }

    /**
     * Calcula a distribuição percentual dos métodos de pagamento
     */
    private function calculatePaymentMethods(): array
    {
        $total = Transaction::where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->count();
        
        if ($total === 0) {
            return [
                'pix' => 0,
                'credit_card' => 0,
                'boleto' => 0,
            ];
        }
        
        $pix = Transaction::where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->where('payment_method', 'like', '%pix%')
            ->count();
        
        $creditCard = Transaction::where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->where(function ($query) {
                $query->where('payment_method', 'like', '%cartão%')
                    ->orWhere('payment_method', 'like', '%card%')
                    ->orWhere('payment_method', 'like', '%credito%')
                    ->orWhere('payment_method', 'like', '%crédito%');
            })
            ->count();
        
        $boleto = Transaction::where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->where('payment_method', 'like', '%boleto%')
            ->count();
        
        return [
            'pix' => round(($pix / $total) * 100, 2),
            'credit_card' => round(($creditCard / $total) * 100, 2),
            'boleto' => round(($boleto / $total) * 100, 2),
        ];
    }
}

