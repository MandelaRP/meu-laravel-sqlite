<?php

declare(strict_types=1);

namespace App\Http\Controllers\User\Withdrawal;

use App\Http\Controllers\Controller;
use App\Models\Acquirer;
use App\Models\PixKey;
use App\Models\SystemSetting;
use App\Services\FullPixService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CreateWithdrawalController extends Controller
{
    public function __construct(
        private readonly FullPixService $fullPixService
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Usuário não autenticado',
                'status' => 'error',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'pix_key_type' => 'required|in:CPF,CNPJ,EMAIL,PHONE,EVP',
            'pix_key' => 'required|string|max:255',
            'pix_key_description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
                'status' => 'error',
            ], 422);
        }

        $amount = (float) $request->input('amount');
        $pixKeyType = $request->input('pix_key_type');
        $pixKey = $request->input('pix_key');
        $pixKeyDescription = $request->input('pix_key_description');

        // Buscar configurações de saque
        $minWithdraw = (float) SystemSetting::get('min_withdraw', 10.00);
        
        // Taxas de saque: usar taxas personalizadas do usuário se disponíveis, senão usar taxas globais
        $fixedWithdrawFee = $user->cash_out_fixed !== null 
            ? (float) $user->cash_out_fixed 
            : (float) SystemSetting::get('fixed_withdraw_fee', 1.00);
        $percentWithdrawFee = $user->cash_out_percentage !== null 
            ? (float) $user->cash_out_percentage 
            : (float) SystemSetting::get('percent_withdraw_fee', 0);
        $transferMode = SystemSetting::get('transfer_mode', 'manual'); // 'manual' ou 'automatico'

        // Determinar qual adquirente usar (preferência do usuário ou global)
        $activeAcquirer = null;
        if ($user->preferred_acquirer) {
            $activeAcquirer = Acquirer::where('slug', $user->preferred_acquirer)->first();
        }
        if (!$activeAcquirer) {
            $activeAcquirer = Acquirer::where('is_active', true)->first();
        }

        // Obter taxa de saque da adquirente
        $taxaAdquirente = 0.00;
        if ($activeAcquirer) {
            // Usar withdrawal_fee se disponível, senão usar fixed_fee como fallback
            $taxaAdquirente = (float) ($activeAcquirer->withdrawal_fee ?? $activeAcquirer->fixed_fee ?? 0.00);
        }

        // Calcular taxa da LuckPay (fixa + percentual)
        $percentFee = ($amount * $percentWithdrawFee) / 100;
        $taxaFixaLuckPay = $fixedWithdrawFee + $percentFee;

        // Aplicar lógica: se taxa LuckPay > taxa adquirente, usar taxa LuckPay (engloba)
        // Senão, usar taxa adquirente (LuckPay não cobre a adquirente)
        if ($taxaFixaLuckPay > $taxaAdquirente) {
            // LuckPay cobra mais que a adquirente → engloba taxa da adquirente
            $taxaTotal = $taxaFixaLuckPay;
            $lucroLuckPay = $taxaFixaLuckPay - $taxaAdquirente;
        } else if ($taxaFixaLuckPay == 0) {
            // LuckPay não cobra taxa → apenas adquirente
            $taxaTotal = $taxaAdquirente;
            $lucroLuckPay = 0.00;
        } else {
            // LuckPay cobra menos que a adquirente → usar apenas taxa da adquirente
            $taxaTotal = $taxaAdquirente;
            $lucroLuckPay = 0.00;
        }
        
        // Valor líquido que o seller receberá (após todas as taxas)
        $netAmount = $amount - $taxaTotal;
        
        // Para compatibilidade com o código existente, manter variáveis antigas
        $totalFee = $taxaTotal; // Total de taxas cobradas ao seller
        $gatewayFee = $lucroLuckPay; // Lucro líquido da LuckPay (após descontar taxa adquirente)

        // IMPORTANTE: Validar valor mínimo configurado (R$ 10,00 padrão)
        // O valor mínimo garante que após a taxa de R$ 5,00, reste pelo menos R$ 5,00 líquidos
        // (exigido pela FullPix)
        if ($amount < $minWithdraw) {
            return response()->json([
                'message' => "O valor mínimo para saque é de R$ " . number_format($minWithdraw, 2, ',', '.') . ". Após a taxa de R$ " . number_format($taxaTotal, 2, ',', '.') . ", você receberá R$ " . number_format($netAmount, 2, ',', '.') . " líquidos.",
                'status' => 'error',
                'min_required' => $minWithdraw,
                'fee' => $taxaTotal,
                'net_amount' => $netAmount,
            ], 422);
        }
        
        // Validar que o valor líquido seja >= R$ 5,00 (mínimo exigido pela FullPix)
        $valorMinimoLiquidoFullPix = 5.00;
        if ($netAmount < $valorMinimoLiquidoFullPix) {
            // Calcular valor mínimo necessário (mínimo líquido + taxas)
            $valorNecessario = $valorMinimoLiquidoFullPix + $taxaTotal;
            return response()->json([
                'message' => "O valor líquido mínimo exigido é R$ " . number_format($valorMinimoLiquidoFullPix, 2, ',', '.') . ". O valor mínimo para saque é de R$ " . number_format($valorNecessario, 2, ',', '.') . " (já incluindo as taxas).",
                'status' => 'error',
                'min_required' => $valorNecessario,
                'min_net' => $valorMinimoLiquidoFullPix,
                'fee' => $taxaTotal,
            ], 422);
        }

        // Validar saldo disponível (usar comparação arredondada para evitar problemas de ponto flutuante)
        // Recarregar o usuário para garantir que temos o saldo mais atualizado
        $user->refresh();
        $userBalance = (float) $user->balance;
        $amountRounded = round($amount, 2);
        $balanceRounded = round($userBalance, 2);
        
        Log::info('Validação de saldo para saque', [
            'user_id' => $user->id,
            'amount' => $amount,
            'amount_rounded' => $amountRounded,
            'user_balance' => $userBalance,
            'balance_rounded' => $balanceRounded,
            'comparison' => $amountRounded > $balanceRounded,
        ]);
        
        if ($amountRounded > $balanceRounded) {
            return response()->json([
                'message' => 'Saldo insuficiente para realizar o saque. Saldo disponível: R$ ' . number_format($balanceRounded, 2, ',', '.'),
                'status' => 'error',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Normalizar chave PIX antes de qualquer operação
            $normalizedPixKey = $this->normalizePixKey($pixKeyType, $pixKey);
            
            if (!$normalizedPixKey) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Chave PIX inválida. Verifique o tipo e o valor informados.',
                    'status' => 'error',
                ], 422);
            }

            $withdrawalId = null;
            $status = 'pending';
            $fullpixResponse = null;
            $errorMessage = null;
            $paidAt = null;

            // Verificar modo de transferência
            if ($transferMode === 'automatico' || $transferMode === 'Saque Automático') {
                // Modo Automático: Chamar API FullPix
                if (!$activeAcquirer || $activeAcquirer->slug !== 'fullpix') {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Saque automático requer FullPix como adquirente ativa.',
                        'status' => 'error',
                    ], 422);
                }

                // Descontar saldo ANTES de chamar API (para bloquear o valor total solicitado)
                // Usar DB::table para garantir que o decrement seja feito diretamente no banco
                DB::table('users')
                    ->where('id', $user->id)
                    ->decrement('balance', $amount);
                
                // Recarregar usuário para garantir que o saldo foi atualizado
                $user->refresh();

                // IMPORTANTE: Enviar o valor líquido (netAmount) à FullPix, não o valor total
                // O valor líquido já tem as taxas descontadas
                $withdrawalResponse = $this->fullPixService->createWithdrawal(
                    $netAmount, // Valor líquido após taxas (o que o seller realmente receberá)
                    $pixKeyType,
                    $normalizedPixKey,
                    'Saque via LuckPay',
                    null
                );

                // Se a API falhar, restaurar saldo e criar saque como pending
                if (!$withdrawalResponse) {
                    // Restaurar saldo se a API falhou (usar DB::table para garantir)
                    DB::table('users')
                        ->where('id', $user->id)
                        ->increment('balance', $amount);
                    
                    // Criar saque como pending mesmo com erro na API
                    $status = 'pending';
                    $errorMessage = 'Erro ao processar saque na adquirente. O saque foi criado como pendente para análise manual.';
                    $fullpixResponse = null;
                } else {
                    // Extrair ID do saque da resposta
                    if (isset($withdrawalResponse['withdrawal']['id'])) {
                        $withdrawalId = $withdrawalResponse['withdrawal']['id'];
                    } elseif (isset($withdrawalResponse['id'])) {
                        $withdrawalId = $withdrawalResponse['id'];
                    }

                    // Extrair status da resposta da FullPix
                    // A resposta pode estar em withdrawal.status, status, ou pode ser sucesso se a API retornou 200
                    $apiStatus = null;
                    if (isset($withdrawalResponse['withdrawal']['status'])) {
                        $apiStatus = strtolower($withdrawalResponse['withdrawal']['status']);
                    } elseif (isset($withdrawalResponse['status'])) {
                        $apiStatus = strtolower($withdrawalResponse['status']);
                    } elseif (isset($withdrawalResponse['data']['status'])) {
                        $apiStatus = strtolower($withdrawalResponse['data']['status']);
                    }
                    
                    $fullpixResponse = json_encode($withdrawalResponse);
                    
                    // Se a API retornou sucesso (200), considerar como aprovado mesmo sem status explícito
                    // ou se o status indicar sucesso
                    if ($apiStatus && in_array($apiStatus, ['success', 'done', 'approved', 'completed', 'paid', 'processed'])) {
                        $status = 'done';
                        $paidAt = now();
                    } elseif (!$apiStatus || $apiStatus === 'pending') {
                        // Se não há status ou é pending, mas a API retornou sucesso (200), considerar como done
                        // (FullPix pode processar instantaneamente)
                        $status = 'done';
                        $paidAt = now();
                    } elseif (in_array($apiStatus, ['failed', 'refused', 'error'])) {
                        // Restaurar saldo se falhou (usar DB::table para garantir)
                        DB::table('users')
                            ->where('id', $user->id)
                            ->increment('balance', $amount);
                        $status = 'pending';
                        $errorMessage = $withdrawalResponse['error']['message'] ?? $withdrawalResponse['message'] ?? 'Saque recusado pela adquirente.';
                        $paidAt = null;
                    } else {
                        // Status intermediário (processing, etc) - considerar como done se a API retornou 200
                        $status = 'done';
                        $paidAt = now();
                    }
                }
            } else {
                // Modo Manual: Apenas criar solicitação, não chamar API
                $status = 'pending';
                // Descontar saldo imediatamente para bloquear o valor
                // Se o admin cancelar, o saldo será restaurado
                // Usar DB::table para garantir que o decrement seja feito diretamente no banco
                DB::table('users')
                    ->where('id', $user->id)
                    ->decrement('balance', $amount);
                
                // Recarregar usuário para garantir que o saldo foi atualizado
                $user->refresh();
            }

            // Criar ou atualizar chave PIX se necessário
            $pixKeyRecord = PixKey::where('user_id', $user->id)
                ->where('type', $pixKeyType)
                ->where('key', $normalizedPixKey)
                ->first();

            if (!$pixKeyRecord) {
                $pixKeyRecord = PixKey::create([
                    'user_id' => $user->id,
                    'type' => $pixKeyType,
                    'key' => $normalizedPixKey,
                    'description' => $pixKeyDescription,
                    'is_active' => true,
                ]);
            }

            // Criar registro no banco
            $withdrawal = DB::table('withdrawals')->insertGetId([
                'user_id' => $user->id,
                'fullpix_withdrawal_id' => $withdrawalId,
                'pix_type' => $pixKeyType,
                'pix_key' => $normalizedPixKey,
                'amount' => $amount, // Valor bruto solicitado (o que foi descontado do saldo)
                'fee' => $totalFee, // Total de taxas cobradas ao seller
                'gateway_fee' => $gatewayFee, // Lucro líquido da LuckPay (taxa total - taxa adquirente)
                'net_amount' => $netAmount, // Valor líquido enviado à FullPix (o que o seller receberá)
                'status' => $status,
                'description' => 'Saque via LuckPay',
                'error_message' => $errorMessage,
                'fullpix_response' => $fullpixResponse,
                'paid_at' => isset($paidAt) ? $paidAt : null, // Data de pagamento quando confirmado
                'is_sample' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Se o saque foi finalizado (status = done), atualizar lucro da plataforma do usuário
            if ($status === 'done' && $gatewayFee > 0) {
                // Usar DB::table para garantir que o increment seja feito diretamente no banco
                DB::table('users')
                    ->where('id', $user->id)
                    ->increment('profit_for_platform', $gatewayFee);
                DB::table('users')
                    ->where('id', $user->id)
                    ->increment('withdraw_amount', $amount);
            }

            // Recarregar usuário para garantir dados atualizados
            $user->refresh();

            DB::commit();

            $message = $transferMode === 'automatico' || $transferMode === 'Saque Automático' 
                ? 'Saque processado automaticamente com sucesso!'
                : 'Saque solicitado com sucesso! Aguardando aprovação do administrador.';

            Log::info('Saque criado com sucesso', [
                'user_id' => $user->id,
                'withdrawal_id' => $withdrawal,
                'amount' => $amount,
                'fee' => $totalFee,
                'net_amount' => $netAmount,
                'mode' => $transferMode,
                'status' => $status,
            ]);

            // Preparar resposta com informações do saque
            $responseData = [
                'message' => $message,
                'status' => 'success',
                'withdrawal_id' => $withdrawal,
                'mode' => $transferMode,
                'withdrawal_status' => $status,
                'amount' => $amount,
                'net_amount' => $netAmount,
                'fee' => $totalFee,
            ];
            
            // Se foi confirmado automaticamente (status = done), incluir flag para modal
            if ($status === 'done' && ($transferMode === 'automatico' || $transferMode === 'Saque Automático')) {
                $responseData['confirmed'] = true;
                $responseData['confirmed_message'] = "Seu saque de R$ " . number_format($netAmount, 2, ',', '.') . " foi processado com sucesso. O valor cairá instantaneamente ou em alguns minutos, dependendo da disponibilidade bancária.";
            }
            
            return response()->json($responseData, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Se o saldo foi descontado antes do erro, restaurar
            try {
                $user->refresh();
                // Verificar se há saque criado mas com erro
                $lastWithdrawal = DB::table('withdrawals')
                    ->where('user_id', $user->id)
                    ->where('created_at', '>=', now()->subMinutes(1))
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($lastWithdrawal && $lastWithdrawal->status === 'pending') {
                    // Se o saque foi criado mas deu erro, manter como pending
                    Log::warning('Saque criado mas houve erro no processamento', [
                        'withdrawal_id' => $lastWithdrawal->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } catch (\Exception $restoreError) {
                Log::error('Erro ao verificar/restaurar saldo', [
                    'error' => $restoreError->getMessage(),
                ]);
            }
            
            Log::error('Erro ao criar saque', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erro ao processar saque: ' . $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    private function mapStatus(string $status): string
    {
        return match(strtolower($status)) {
            'pending' => 'pending',
            'approved' => 'approved',
            'processing' => 'processing',
            'done', 'done_manual' => 'done',
            'failed' => 'failed',
            'refused' => 'refused',
            'cancelled' => 'cancelled',
            default => 'pending',
        };
    }

    /**
     * Normaliza a chave PIX baseado no tipo
     */
    private function normalizePixKey(string $type, string $key): ?string
    {
        return match($type) {
            'CPF' => preg_replace('/[^0-9]/', '', $key), // Apenas números, 11 dígitos
            'CNPJ' => preg_replace('/[^0-9]/', '', $key), // Apenas números, 14 dígitos
            'EMAIL' => strtolower(trim($key)), // Email em minúsculas
            'PHONE' => $this->normalizePhone($key), // Telefone com código do país
            'EVP' => $key, // UUID já está no formato correto
            default => null,
        };
    }

    /**
     * Normaliza telefone para formato +55XXXXXXXXXXX
     */
    private function normalizePhone(string $phone): string
    {
        // Remove tudo exceto números
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Se já começa com 55, adiciona +
        if (str_starts_with($cleaned, '55')) {
            return '+' . $cleaned;
        }
        
        // Se tem 10 ou 11 dígitos, adiciona +55
        if (strlen($cleaned) >= 10 && strlen($cleaned) <= 11) {
            return '+55' . $cleaned;
        }
        
        return $phone;
    }
}
