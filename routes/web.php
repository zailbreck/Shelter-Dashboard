<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// Guest routes (not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// 2FA verification (after login, before full authentication)
Route::middleware('web')->group(function () {
    Route::get('/2fa/verify', [LoginController::class, 'show2faForm'])->name('2fa.verify');
    Route::post('/2fa/verify', [LoginController::class, 'verify2fa']);
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // 2FA setup (mandatory for new users)
    Route::get('/2fa/setup', [TwoFactorController::class, 'showSetup'])->name('2fa.setup');
    Route::post('/2fa/setup', [TwoFactorController::class, 'verifySetup'])->name('2fa.verify-setup');

    // Routes that require 2FA to be enabled
    Route::middleware('require2fa')->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/agents/{agent}', [DashboardController::class, 'show'])->name('agent.show');
        Route::delete('/agents/{agent}', [DashboardController::class, 'destroy'])->name('agent.delete');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::post('/settings/password', [SettingsController::class, 'changePassword'])->name('settings.password');
        Route::post('/settings/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
        Route::post('/settings/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
    });

    // Logout (doesn't require 2FA)
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
