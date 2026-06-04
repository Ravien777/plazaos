<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadConversionController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\LeadExportController;
use App\Http\Controllers\LeadImportController;
use App\Http\Controllers\LeadSourceController;
use App\Http\Controllers\LeadBulkController;
use App\Http\Controllers\ClientBulkController;
use App\Http\Controllers\ClientExportController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PortalUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReplyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::post('/webhooks/resend', [\App\Http\Controllers\ResendWebhookController::class, 'handle']);

Route::get('/invite/{token}', [InvitationController::class, 'show'])->name('invitation.show');
Route::post('/invite/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/onboard/team', [TeamController::class, 'create'])->name('team.create');
    Route::post('/onboard/team', [TeamController::class, 'store'])->name('team.store');
    Route::get('/team/settings', [TeamController::class, 'edit'])->name('team.edit');
    Route::put('/team/settings', [TeamController::class, 'update'])->name('team.update');
    Route::get('/team/members', [MemberController::class, 'index'])->name('team.members');
    Route::post('/team/members/invite', [InvitationController::class, 'store'])->name('team.members.invite');
    Route::delete('/team/members/{user}', [MemberController::class, 'destroy'])->name('team.members.remove');
    Route::delete('/team/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('team.invitations.destroy');

    Route::get('/settings/integrations', [SettingsController::class, 'edit'])->name('settings.integrations');
    Route::post('/settings/integrations', [SettingsController::class, 'update']);

    Route::get('/leads/export', [LeadExportController::class, 'export'])->name('leads.export');
    Route::post('/leads/bulk/delete', [LeadBulkController::class, 'destroy'])->middleware('throttle:5,1')->name('leads.bulk.delete');
    Route::post('/leads/bulk/delete-by-filters', [LeadBulkController::class, 'destroyByFilters'])->middleware('throttle:3,1')->name('leads.bulk.delete-by-filters');
    Route::post('/leads/bulk/status', [LeadBulkController::class, 'updateStatus'])->middleware('throttle:5,1')->name('leads.bulk.status');
    Route::post('/leads/bulk/email', [LeadBulkController::class, 'sendEmail'])->middleware('throttle:5,1')->name('leads.bulk.email');

    Route::get('/leads/import', [LeadImportController::class, 'create'])->name('leads.import.create');
    Route::post('/leads/import/preview', [LeadImportController::class, 'preview'])->name('leads.import.preview');
    Route::post('/ai/leads/{lead}/generate-outreach', [AiController::class, 'generateOutreach'])->middleware('throttle:10,1')->name('ai.generate-outreach');
    Route::post('/ai/leads/{lead}/generate-follow-up', [AiController::class, 'generateFollowUp'])->middleware('throttle:10,1')->name('ai.generate-follow-up');
    Route::post('/ai/leads/{lead}/summarize-website', [AiController::class, 'summarizeWebsite'])->middleware('throttle:5,1')->name('ai.summarize-website');
    Route::post('/ai/clients/{client}/generate-outreach', [AiController::class, 'generateClientOutreach'])->middleware('throttle:10,1')->name('ai.client-generate-outreach');
    Route::post('/ai/clients/{client}/generate-follow-up', [AiController::class, 'generateClientFollowUp'])->middleware('throttle:10,1')->name('ai.client-generate-follow-up');
    Route::post('/ai/bulk/generate-outreach', [AiController::class, 'bulkGenerateOutreach'])->middleware('throttle:5,1')->name('ai.bulk-generate-outreach');

    Route::post('/leads/import', [LeadImportController::class, 'import'])->middleware('throttle:3,1')->name('leads.import.store');
    Route::get('/leads/import/{import}/progress', [LeadImportController::class, 'progress'])->name('leads.import.progress');
    Route::get('/leads/import/{import}', [LeadImportController::class, 'show'])->name('leads.import.show');

    Route::resource('leads', LeadController::class);

    Route::resource('lead-sources', LeadSourceController::class);
    Route::post('/lead-sources/{leadSource}/run', [LeadSourceController::class, 'run'])->middleware('throttle:3,1')->name('lead-sources.run');

    Route::get('/clients/export', [ClientExportController::class, 'export'])->name('clients.export');
    Route::post('/clients/bulk/delete', [ClientBulkController::class, 'destroy'])->middleware('throttle:5,1')->name('clients.bulk.delete');
    Route::post('/clients/bulk/status', [ClientBulkController::class, 'updateStatus'])->middleware('throttle:5,1')->name('clients.bulk.status');

    Route::resource('clients', ClientController::class);
    Route::get('/clients/{client}/portal-users', [PortalUserController::class, 'index'])->name('clients.portal-users.index');
    Route::post('/clients/{client}/portal-users', [PortalUserController::class, 'store'])->name('clients.portal-users.store');
    Route::delete('/clients/{client}/portal-users/{portalUser}', [PortalUserController::class, 'destroy'])->name('clients.portal-users.destroy');
    Route::post('/leads/{lead}/convert', [LeadConversionController::class, 'convert'])->name('leads.convert');

    Route::resource('projects', ProjectController::class);

    Route::get('/meetings/upcoming', [MeetingController::class, 'upcoming'])->name('meetings.upcoming');
    Route::get('/{meetableType}/{meetable}/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::post('/{meetableType}/{meetable}/meetings', [MeetingController::class, 'storeForMeetable'])->name('meetings.store_for_meetable');
    Route::resource('meetings', MeetingController::class)->except(['index']);

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');

    Route::get('/api/entity-search', [TicketController::class, 'searchEntities'])->name('api.entity-search');

    Route::get('/{documentableType}/{documentable}/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->middleware('throttle:10,1')->name('documents.store');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::get('/email-templates', [EmailController::class, 'templates'])->name('email.templates');
    Route::get('/{emailableType}/{emailable}/emails', [EmailController::class, 'index'])->name('emails.index');
    Route::post('/{emailableType}/{emailable}/emails', [EmailController::class, 'store'])->middleware('throttle:5,1')->name('emails.store');

    Route::get('/{noteableType}/{noteable}/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::post('/{noteableType}/{noteable}/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    Route::resource('tickets', TicketController::class);
    Route::post('/tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
    Route::post('/tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
    Route::post('/tickets/{ticket}/replies', [TicketReplyController::class, 'store'])->name('tickets.replies.store');

    Route::resource('templates', EmailTemplateController::class)->except(['show']);

    Route::resource('intake-forms', \App\Http\Controllers\IntakeFormController::class);
    Route::get('/intake-forms/{intakeForm}/submissions/{submission}', [\App\Http\Controllers\IntakeFormSubmissionController::class, 'show'])->name('intake-forms.submissions.show');
    Route::get('/intake-forms/submissions/download', [\App\Http\Controllers\IntakeFormSubmissionController::class, 'download'])->name('intake-forms.submissions.download');

});

require __DIR__.'/auth.php';
