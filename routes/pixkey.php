<?php

declare(strict_types = 1);

use App\Http\Controllers\User\PixKey\DestroyPixKeyController;
use App\Http\Controllers\User\PixKey\IndexPixKeysController;
use App\Http\Controllers\User\PixKey\StorePixKeyController;
use App\Http\Controllers\User\PixKey\UpdatePixKeyController;
use Illuminate\Support\Facades\Route;

Route::get('pix-keys', IndexPixKeysController::class)->name('pix-keys.index');
Route::post('pix-keys', StorePixKeyController::class)->name('pix-keys.store');
Route::post('pix-keys/{id}', UpdatePixKeyController::class)->name('pix-keys.update');
Route::delete('pix-keys/{id}', DestroyPixKeyController::class)->name('pix-keys.destroy');

