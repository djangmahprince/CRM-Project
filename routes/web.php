<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Middleware\EnsureSessionIsActive;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware(['auth', EnsureSessionIsActive::class])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');

    Route::resource('leads', LeadController::class);
    Route::resource('accounts', AccountController::class);
    Route::resource('contacts', ContactController::class);
    Route::resource('opportunities', OpportunityController::class);
    Route::resource('cases', CaseController::class);
    Route::post('/cases/{case}/close', [CaseController::class, 'close'])->name('cases.close');
    Route::post('/cases/{case}/reopen', [CaseController::class, 'reopen'])->name('cases.reopen');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{report}/export', [ReportController::class, 'export'])->name('reports.export');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
