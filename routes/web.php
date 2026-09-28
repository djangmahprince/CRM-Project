<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\BulkActionController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadConversionController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\ReportBuilderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TaskController;
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
    Route::get('/leads/{lead}/convert', [LeadConversionController::class, 'create'])->name('leads.convert');
    Route::post('/leads/{lead}/convert', [LeadConversionController::class, 'store'])->name('leads.convert.store');

    Route::resource('accounts', AccountController::class);
    Route::resource('contacts', ContactController::class);
    Route::resource('opportunities', OpportunityController::class);
    Route::resource('cases', CaseController::class);
    Route::post('/cases/{case}/close', [CaseController::class, 'close'])->name('cases.close');
    Route::post('/cases/{case}/reopen', [CaseController::class, 'reopen'])->name('cases.reopen');

    Route::resource('tasks', TaskController::class);
    Route::resource('events', EventController::class);
    Route::patch('/events/{event}/reschedule', [EventController::class, 'reschedule'])->name('events.reschedule');
    Route::get('/calendar', CalendarController::class)->name('calendar');

    Route::post('/bulk-actions', BulkActionController::class)->name('bulk-actions');

    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
    Route::post('/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    Route::get('/import', [ImportController::class, 'create'])->name('import.create');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/builder', [ReportBuilderController::class, 'create'])->name('report-builder.create');
    Route::post('/reports/builder', [ReportBuilderController::class, 'store'])->name('report-builder.store');
    Route::get('/reports/builder/{report}', [ReportBuilderController::class, 'show'])->name('report-builder.show');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{report}/export', [ReportController::class, 'export'])->name('reports.export');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
