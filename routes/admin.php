<?php

declare(strict_types = 1);

use App\Http\Controllers\Admin\Dashboard\IndexAdminDashboardController;
use App\Http\Controllers\Admin\Products\IndexProductsController;
use App\Http\Controllers\Admin\Products\DestroyProductController;
use App\Http\Controllers\Admin\Transactions\IndexTransactionsController;
use App\Http\Controllers\Admin\Withdrawals\IndexWithdrawalsController;
use App\Http\Controllers\Admin\Settings\IndexSettingsController;
use App\Http\Controllers\Admin\Images\IndexImagesController;
use App\Http\Controllers\Admin\Acquirers\IndexAcquirersController;
use App\Http\Controllers\Admin\Acquirers\ToggleAcquirerController;
use App\Http\Controllers\Admin\Acquirers\UpdateAcquirerController;
use App\Http\Controllers\Admin\Acquirers\CheckApiStatusController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function (): void {
    Route::get('/dashboard', IndexAdminDashboardController::class)->name('admin.dashboard');
    Route::get('/products', IndexProductsController::class)->name('admin.products.index');
    Route::delete('/products/{product}', DestroyProductController::class)->name('admin.products.destroy');
    Route::get('/transactions', IndexTransactionsController::class)->name('admin.transactions.index');
    Route::get('/transactions/{id}', App\Http\Controllers\Admin\Transactions\ShowTransactionController::class)->name('admin.transactions.show');
    Route::get('/withdrawals', IndexWithdrawalsController::class)->name('admin.withdrawals.index');
    Route::post('/withdrawals/{id}/approve', App\Http\Controllers\Admin\Withdrawals\ApproveWithdrawalController::class)->name('admin.withdrawals.approve');
    Route::post('/withdrawals/{id}/cancel', App\Http\Controllers\Admin\Withdrawals\CancelWithdrawalController::class)->name('admin.withdrawals.cancel');
    Route::get('/settings', IndexSettingsController::class)->name('admin.settings.index');
    Route::post('/settings', App\Http\Controllers\Admin\Settings\UpdateSettingsController::class)->name('admin.settings.update');
    Route::get('/images', IndexImagesController::class)->name('admin.images.index');
    Route::post('/images', App\Http\Controllers\Admin\Images\StoreImagesController::class)->name('admin.images.store');
    
    // Adquirentes
    Route::get('/acquirers', IndexAcquirersController::class)->name('admin.acquirers.index');
    Route::put('/acquirers/{id}/toggle', ToggleAcquirerController::class)->name('admin.acquirers.update-toggle');
    Route::post('/acquirers/{id}/update', UpdateAcquirerController::class)->name('admin.acquirers.update');
    Route::post('/acquirers/{id}/check-status', CheckApiStatusController::class)->name('admin.acquirers.check-status');
});

