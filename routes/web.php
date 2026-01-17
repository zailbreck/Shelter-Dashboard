<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/agent/{agent}', [DashboardController::class, 'show'])->name('agent.show');
Route::delete('/agent/{agent}', [DashboardController::class, 'destroy'])->name('agent.delete');
