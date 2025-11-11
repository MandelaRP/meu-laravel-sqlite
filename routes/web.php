<?php

declare(strict_types = 1);

use App\Http\Controllers\GroupController;
use App\Http\Controllers\Onboarding\OnboardingStore;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::group(['middleware' => ['auth', 'verified', 'recent_user']], function (): void {
    Route::get('dashboard', App\Http\Controllers\Dashboard\IndexDashboardController::class)->name('dashboard');
    Route::resource('groups', GroupController::class);
    Route::resource('team', TeamController::class);

    Route::post('sale/generate-charge', App\Http\Controllers\User\Sale\GenerateChargeController::class)->name('sale.generate-charge');
    Route::get('sale/check-status/{saleId}', App\Http\Controllers\User\Sale\CheckPaymentStatusController::class)->name('sale.check-status');

    // Rotas de estatísticas
    Route::get('/api/dashboard/seller/stats', App\Http\Controllers\Dashboard\SellerStatsController::class)->name('api.dashboard.seller.stats');
    Route::get('/api/dashboard/admin/stats', App\Http\Controllers\Dashboard\AdminStatsController::class)->name('api.dashboard.admin.stats');
    Route::get('/api/transactions/count', App\Http\Controllers\Transaction\TransactionCountController::class)->name('api.transactions.count');
    Route::get('/api/withdrawals/count', App\Http\Controllers\Withdrawal\WithdrawalCountController::class)->name('api.withdrawals.count');
    Route::get('/api/users/count', App\Http\Controllers\User\UserCountController::class)->name('api.users.count');

    require __DIR__ . '/seller.php';

    require __DIR__ . '/transaction.php';

    require __DIR__ . '/users.php';

    require __DIR__ . '/checkout.php';

    require __DIR__ . '/pixkey.php';

    Route::post('withdrawal/create', App\Http\Controllers\User\Withdrawal\CreateWithdrawalController::class)->name('withdrawal.create');

    require __DIR__ . '/admin.php';
});

Route::get('onboarding', fn () => Inertia::render('Onboarding/Index'))
    ->middleware(['auth', 'verified'])->name('onboarding');

Route::post('onboarding', OnboardingStore::class)
    ->middleware(['auth', 'verified'])->name('onboarding.store');

require __DIR__ . '/settings.php';

require __DIR__ . '/auth.php';

// Webhooks (sem autenticação)
Route::post('webhook/liberpay', App\Http\Controllers\Webhook\LiberpayWebhookController::class)
    ->name('webhook.liberpay')
    ->middleware('throttle:60,1');

Route::post('webhook/fullpix', App\Http\Controllers\Webhook\FullPixWebhookController::class)
    ->name('webhook.fullpix')
    ->middleware('throttle:60,1');
