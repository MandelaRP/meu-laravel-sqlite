<?php

declare(strict_types = 1);

use App\Http\Controllers\User\Transaction\IndexTransactionController;
use App\Http\Controllers\User\Transaction\ShowTransactionController;
use Illuminate\Support\Facades\Route;

Route::get('transactions', IndexTransactionController::class)->name('transactions.index');
Route::get('transactions/{id}', ShowTransactionController::class)->name('transactions.show');
