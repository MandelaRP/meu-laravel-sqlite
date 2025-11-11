<?php

declare(strict_types = 1);

use App\Http\Controllers\Seller\Checkout\CreateCheckoutController;
use App\Http\Controllers\Seller\Checkout\DestroyCheckoutController;
use App\Http\Controllers\Seller\Checkout\EditCheckoutController;
use App\Http\Controllers\Seller\Checkout\IndexCheckoutController;
use App\Http\Controllers\Seller\Checkout\StoreCheckoutController;
use App\Http\Controllers\Seller\Checkout\UpdateCheckoutController;
use App\Http\Controllers\Seller\Financial\IndexFinancialController;
use App\Http\Controllers\Seller\Products\CreateProductController;
use App\Http\Controllers\Seller\Products\DestroyProductController;
use App\Http\Controllers\Seller\Products\EditProductController;
use App\Http\Controllers\Seller\Products\IndexProductController;
use App\Http\Controllers\Seller\Products\StoreProductController;
use App\Http\Controllers\Seller\Products\UpdateProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('seller')->group(function (): void {
    // Rotas de Financeiro
    Route::get('/financial', IndexFinancialController::class)->name('financial.index');

    // Rotas de Produtos
    Route::get('/products', IndexProductController::class)->name('products.index');
    Route::get('/products/create', CreateProductController::class)->name('products.create');
    Route::post('/products', StoreProductController::class)->name('products.store');
    Route::get('/products/{product}/edit', EditProductController::class)->name('products.edit');
    Route::post('/products/{product}', UpdateProductController::class)->name('products.update');
    Route::delete('/products/{product}', DestroyProductController::class)->name('products.destroy');

    // Rotas de Checkout
    Route::get('/checkout', IndexCheckoutController::class)->name('checkout.index');
    Route::get('/checkout/create', CreateCheckoutController::class)->name('checkout.create');
    Route::post('/checkout', StoreCheckoutController::class)->name('checkout.store');
    Route::get('/checkout/{checkout}/edit', EditCheckoutController::class)->name('checkout.edit');
    Route::post('/checkout/{checkout}', UpdateCheckoutController::class)->name('checkout.update');
    Route::delete('/checkout/{checkout}', DestroyCheckoutController::class)->name('checkout.destroy');
});
