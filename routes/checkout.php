<?php

declare(strict_types = 1);

use App\Http\Controllers\Checkout\ShowCheckoutController;
use App\Http\Controllers\Checkout\ProcessPaymentController;
use App\Http\Controllers\Checkout\PixPaymentController;
use Illuminate\Support\Facades\Route;

// Rota pública para checkout
Route::get('/checkout/{id}', ShowCheckoutController::class)->name('checkout.show');
Route::post('/checkout/{id}/process-payment', ProcessPaymentController::class)->name('checkout.process-payment');

// Rota pública para pagamento PIX
Route::get('/pagamento/pix', PixPaymentController::class)->name('payment.pix');
