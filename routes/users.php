<?php

declare(strict_types = 1);

use App\Http\Controllers\Admin\User\IndexUserController;
use App\Http\Controllers\Admin\User\ShowUserController;
use App\Http\Controllers\Admin\User\UpdateUserController;
use App\Http\Controllers\Admin\User\DestroyUserController;
use Illuminate\Support\Facades\Route;

Route::get('/usuarios', IndexUserController::class)->name('users.index');
Route::get('/usuarios/{user}', ShowUserController::class)->name('users.show');
Route::match(['put', 'patch'], '/usuarios/{user}', UpdateUserController::class)->name('users.update');
Route::match(['put', 'patch'], '/usuarios/{user}/balance', App\Http\Controllers\Admin\User\UpdateUserBalanceController::class)->name('users.update-balance');
Route::delete('/usuarios/{user}', DestroyUserController::class)->name('users.destroy');
Route::get('/usuarios/{user}/stats', App\Http\Controllers\Admin\User\UserStatsController::class)->name('users.stats');
Route::post('/usuarios/{user}/set-admin', [App\Http\Controllers\Admin\User\UserActionsController::class, 'setAdmin'])->name('users.set-admin');
Route::post('/usuarios/{user}/ban', [App\Http\Controllers\Admin\User\UserActionsController::class, 'ban'])->name('users.ban');
Route::post('/usuarios/{user}/activate', [App\Http\Controllers\Admin\User\UserActionsController::class, 'activate'])->name('users.activate');
Route::post('/usuarios/{user}/reject', [App\Http\Controllers\Admin\User\UserActionsController::class, 'reject'])->name('users.reject');
