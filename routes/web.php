<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountHierarchyController;
use App\Http\Controllers\AdvancedSearchController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\BulkActionController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CrmDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GdprController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadConversionController;
use App\Http\Controllers\MfaController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\ReportBuilderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportSubscriptionController;
use App\Http\Controllers\SavedSearchController;
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

    Route::get('/mfa/challenge', [MfaController::class, 'challenge'])->name('mfa.challenge');
    Route::post('/mfa/challenge', [MfaController::class, 'verifyChallenge'])->name('mfa.challenge.verify')->middleware('throttle:10,1');
});

Route::middleware(['auth', EnsureSessionIsActive::class])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');
    Route::get('/search/advanced', [AdvancedSearchController::class, 'index'])->name('search.advanced');
    Route::post('/saved-searches', [SavedSearchController::class, 'store'])->name('saved-searches.store');
    Route::delete('/saved-searches/{savedSearch}', [SavedSearchController::class, 'destroy'])->name('saved-searches.destroy');

    Route::resource('leads', LeadController::class);
    Route::get('/leads/{lead}/convert', [LeadConversionController::class, 'create'])->name('leads.convert');
    Route::post('/leads/{lead}/convert', [LeadConversionController::class, 'store'])->name('leads.convert.store');

    Route::get('/accounts/hierarchy', [AccountHierarchyController::class, 'index'])->name('accounts.hierarchy');
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
    Route::get('/attachments/{attachment}/preview', [AttachmentController::class, 'preview'])->name('attachments.preview');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    Route::get('/import', [ImportController::class, 'create'])->name('import.create');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/builder', [ReportBuilderController::class, 'create'])->name('report-builder.create');
    Route::post('/reports/builder', [ReportBuilderController::class, 'store'])->name('report-builder.store');
    Route::get('/reports/builder/{report}', [ReportBuilderController::class, 'show'])->name('report-builder.show');
    Route::post('/reports/{report}/subscriptions', [ReportSubscriptionController::class, 'store'])->name('report-subscriptions.store');
    Route::delete('/report-subscriptions/{subscription}', [ReportSubscriptionController::class, 'destroy'])->name('report-subscriptions.destroy');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{report}/export', [ReportController::class, 'export'])->name('reports.export');

    Route::get('/crm-dashboards', [CrmDashboardController::class, 'index'])->name('crm-dashboards.index');
    Route::get('/crm-dashboards/create', [CrmDashboardController::class, 'create'])->name('crm-dashboards.create');
    Route::post('/crm-dashboards', [CrmDashboardController::class, 'store'])->name('crm-dashboards.store');
    Route::get('/crm-dashboards/{dashboard}', [CrmDashboardController::class, 'show'])->name('crm-dashboards.show');
    Route::delete('/crm-dashboards/{dashboard}', [CrmDashboardController::class, 'destroy'])->name('crm-dashboards.destroy');

    Route::get('/settings/mfa', [MfaController::class, 'edit'])->name('mfa.edit');
    Route::post('/settings/mfa', [MfaController::class, 'confirm'])->name('mfa.confirm');
    Route::delete('/settings/mfa', [MfaController::class, 'destroy'])->name('mfa.destroy');

    Route::get('/admin/gdpr', [GdprController::class, 'index'])->name('gdpr.index');
    Route::get('/admin/gdpr/{user}/export', [GdprController::class, 'export'])->name('gdpr.export');
    Route::delete('/admin/gdpr/{user}', [GdprController::class, 'destroy'])->name('gdpr.destroy');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
