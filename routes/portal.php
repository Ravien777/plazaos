<?php

declare(strict_types=1);

use App\Http\Controllers\Portal\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('portal')->name('portal.')->group(function (): void {
    Route::middleware('guest:client')->group(function (): void {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);
    });

    Route::middleware('ensure.client')->group(function (): void {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('dashboard', [\App\Http\Controllers\Portal\DashboardController::class, 'index'])->name('dashboard');

        Route::get('projects', [\App\Http\Controllers\Portal\ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/{project}', [\App\Http\Controllers\Portal\ProjectController::class, 'show'])->name('projects.show');

        Route::get('documents', [\App\Http\Controllers\Portal\DocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/{document}/download', [\App\Http\Controllers\Portal\DocumentController::class, 'download'])->name('documents.download');

        Route::get('meetings', [\App\Http\Controllers\Portal\MeetingController::class, 'index'])->name('meetings.index');

        Route::resource('tickets', \App\Http\Controllers\Portal\TicketController::class)
            ->only(['index', 'create', 'store', 'show']);
        Route::post('tickets/{ticket}/replies', [\App\Http\Controllers\Portal\TicketReplyController::class, 'store'])->name('tickets.replies.store');

        Route::get('intake-forms', [\App\Http\Controllers\Portal\FormController::class, 'index'])->name('intake-forms.index');
        Route::get('intake-forms/{intakeForm}', [\App\Http\Controllers\Portal\FormController::class, 'show'])->name('intake-forms.show');
        Route::post('intake-forms/{intakeForm}', [\App\Http\Controllers\Portal\FormController::class, 'submit'])->name('intake-forms.submit');
    });
});
