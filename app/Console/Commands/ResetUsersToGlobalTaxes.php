<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Console\Command;

class ResetUsersToGlobalTaxes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:reset-to-global-taxes 
                            {--dry-run : Apenas mostra o que seria alterado sem fazer alterações}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reseta todos os usuários para usar taxas globais (remove taxas personalizadas que são iguais às globais)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 MODO DRY-RUN: Nenhuma alteração será feita');
            $this->newLine();
        }

        // Buscar taxas globais
        $globalCashInPercentage = (float) (SystemSetting::where('key', 'gateway_pix_percentage')->first()?->value ?? 0);
        $globalCashInFixed = (float) (SystemSetting::where('key', 'gateway_pix_fixed')->first()?->value ?? 0);
        $globalCashOutPercentage = (float) (SystemSetting::where('key', 'percent_withdraw_fee')->first()?->value ?? 0);
        $globalCashOutFixed = (float) (SystemSetting::where('key', 'fixed_withdraw_fee')->first()?->value ?? 0);

        $this->info('📊 Taxas Globais:');
        $this->line("  Cash In: {$globalCashInPercentage}% + R$ " . number_format($globalCashInFixed, 2, ',', '.'));
        $this->line("  Cash Out: {$globalCashOutPercentage}% + R$ " . number_format($globalCashOutFixed, 2, ',', '.'));
        $this->newLine();

        // Função auxiliar para comparar valores
        $compareValues = function ($val1, $val2) {
            return abs((floatval($val1) ?: 0) - (floatval($val2) ?: 0)) < 0.01;
        };

        // Buscar todos os usuários
        $users = User::all();
        $updated = 0;
        $alreadyGlobal = 0;

        $this->info("🔍 Verificando {$users->count()} usuários...");
        $this->newLine();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $needsUpdate = false;
            $updates = [];

            // Verificar Cash In Percentage
            if ($user->cash_in_percentage !== null) {
                $userVal = (float) $user->cash_in_percentage;
                if ($compareValues($userVal, $globalCashInPercentage)) {
                    $updates['cash_in_percentage'] = null;
                    $needsUpdate = true;
                }
            }

            // Verificar Cash In Fixed
            if ($user->cash_in_fixed !== null) {
                $userVal = (float) $user->cash_in_fixed;
                if ($compareValues($userVal, $globalCashInFixed)) {
                    $updates['cash_in_fixed'] = null;
                    $needsUpdate = true;
                }
            }

            // Verificar Cash Out Percentage
            if ($user->cash_out_percentage !== null) {
                $userVal = (float) $user->cash_out_percentage;
                if ($compareValues($userVal, $globalCashOutPercentage)) {
                    $updates['cash_out_percentage'] = null;
                    $needsUpdate = true;
                }
            }

            // Verificar Cash Out Fixed
            if ($user->cash_out_fixed !== null) {
                $userVal = (float) $user->cash_out_fixed;
                if ($compareValues($userVal, $globalCashOutFixed)) {
                    $updates['cash_out_fixed'] = null;
                    $needsUpdate = true;
                }
            }

            if ($needsUpdate) {
                if (!$dryRun) {
                    $user->update($updates);
                }
                $updated++;
            } else {
                $alreadyGlobal++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("✅ DRY-RUN: {$updated} usuários seriam atualizados para usar taxas globais");
            $this->info("✅ {$alreadyGlobal} usuários já estão usando taxas globais");
        } else {
            $this->info("✅ {$updated} usuários atualizados para usar taxas globais");
            $this->info("✅ {$alreadyGlobal} usuários já estavam usando taxas globais");
        }

        return Command::SUCCESS;
    }
}

