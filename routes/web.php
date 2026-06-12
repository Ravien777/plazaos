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
use App\Http\Controllers\MaroniController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\LeadExportController;
use App\Http\Controllers\LeadImportController;
use App\Http\Controllers\LeadSourceController;
use App\Http\Controllers\LeadBulkController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ClientBulkController;
use App\Http\Controllers\ClientExportController;
use App\Http\Controllers\ProjectBulkController;
use App\Http\Controllers\TicketBulkController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Portal\PortalLinkController;
use App\Http\Controllers\PortalUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SecuritySettingsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReplyController;
use App\Http\Controllers\TrelloController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/about', function () {
    return Inertia::render('About', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('about');

Route::get('/features', function () {
    return Inertia::render('Features', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('features');

Route::get('/pricing', function () {
    return Inertia::render('Pricing', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'plans' => \App\Models\Plan::where('is_active', true)->orderBy('sort_order')->get(),
    ]);
})->name('pricing');

Route::post('/webhooks/resend', [\App\Http\Controllers\ResendWebhookController::class, 'handle']);
Route::post('/api/maroni/webhook', [\App\Http\Controllers\MaroniWebhookController::class, 'handle']);
Route::post('/webhooks/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handle']);

Route::get('/invite/{token}', [InvitationController::class, 'show'])->name('invitation.show');
Route::post('/invite/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::post('/dashboard/layout', [\App\Http\Controllers\DashboardLayoutController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.layout.update');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('onboarding')->group(function () {
        Route::put('/step/{step}', [OnboardingController::class, 'updateStep'])->name('onboarding.step');
        Route::post('/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');
        Route::post('/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
        Route::post('/dismiss', [OnboardingController::class, 'dismiss'])->name('onboarding.dismiss');
        Route::post('/wizard-seen', [OnboardingController::class, 'wizardSeen'])->name('onboarding.wizard-seen');
    });

    Route::get('/onboard/team', [TeamController::class, 'create'])->name('team.create');
    Route::post('/onboard/team', [TeamController::class, 'store'])->name('team.store');
    Route::get('/team/settings', [TeamController::class, 'edit'])->name('team.edit');
    Route::put('/team/settings', [TeamController::class, 'update'])->name('team.update');
    Route::delete('/team/settings', [TeamController::class, 'destroy'])->name('team.destroy');
    Route::get('/team/members', [MemberController::class, 'index'])->name('team.members');
    Route::post('/team/members/invite', [InvitationController::class, 'store'])->name('team.members.invite');
    Route::post('/team/leave', [MemberController::class, 'leave'])->name('team.leave');
    Route::delete('/team/members/{user}', [MemberController::class, 'destroy'])->name('team.members.remove');
    Route::delete('/team/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('team.invitations.destroy');

    Route::get('/settings/integrations', [SettingsController::class, 'edit'])->name('settings.integrations');
    Route::post('/settings/integrations', [SettingsController::class, 'update']);

    Route::get('/settings/notifications', [NotificationPreferenceController::class, 'index'])->name('settings.notifications');
    Route::put('/settings/notifications', [NotificationPreferenceController::class, 'update']);

    Route::get('/settings/security', [SecuritySettingsController::class, 'index'])->name('settings.security');

    Route::get('/settings/billing', [\App\Http\Controllers\BillingController::class, 'index'])->name('settings.billing');
    Route::post('/billing/checkout/{plan}', [\App\Http\Controllers\BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/success', [\App\Http\Controllers\BillingController::class, 'success'])->name('billing.success');
    Route::get('/billing/cancel', [\App\Http\Controllers\BillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('/billing/portal', [\App\Http\Controllers\BillingController::class, 'portal'])->name('billing.portal');
    Route::post('/billing/cancel-subscription', [\App\Http\Controllers\BillingController::class, 'cancelSubscription'])->name('billing.cancel-subscription');
    Route::post('/billing/resume-subscription', [\App\Http\Controllers\BillingController::class, 'resumeSubscription'])->name('billing.resume-subscription');

    Route::get('/settings/webhooks', [WebhookController::class, 'index'])->name('settings.webhooks');
    Route::get('/settings/webhooks/create', [WebhookController::class, 'create'])->name('settings.webhooks.create');
    Route::post('/settings/webhooks', [WebhookController::class, 'store'])->name('settings.webhooks.store');
    Route::get('/settings/webhooks/{webhook}/edit', [WebhookController::class, 'edit'])->name('settings.webhooks.edit');
    Route::put('/settings/webhooks/{webhook}', [WebhookController::class, 'update'])->name('settings.webhooks.update');
    Route::post('/settings/webhooks/{webhook}/test', [WebhookController::class, 'test'])->name('settings.webhooks.test');
    Route::delete('/settings/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('settings.webhooks.destroy');

    Route::get('/leads/export', [LeadExportController::class, 'export'])->name('leads.export');
    Route::post('/leads/bulk/delete', [LeadBulkController::class, 'destroy'])->middleware('throttle:5,1')->name('leads.bulk.delete');
    Route::post('/leads/bulk/delete-by-filters', [LeadBulkController::class, 'destroyByFilters'])->middleware('throttle:3,1')->name('leads.bulk.delete-by-filters');
    Route::post('/leads/bulk/status', [LeadBulkController::class, 'updateStatus'])->middleware('throttle:5,1')->name('leads.bulk.status');
    Route::post('/leads/bulk/email', [LeadBulkController::class, 'sendEmail'])->middleware('throttle:5,1')->name('leads.bulk.email');
    Route::post('/leads/bulk/force-delete', [LeadBulkController::class, 'forceDestroy'])->middleware('throttle:3,1')->name('leads.bulk.force-delete');

    Route::post('/projects/bulk/delete', [ProjectBulkController::class, 'destroy'])->middleware('throttle:5,1')->name('projects.bulk.delete');
    Route::post('/projects/bulk/force-delete', [ProjectBulkController::class, 'forceDestroy'])->middleware('throttle:3,1')->name('projects.bulk.force-delete');
    Route::post('/projects/bulk/status', [ProjectBulkController::class, 'updateStatus'])->middleware('throttle:5,1')->name('projects.bulk.status');

    Route::post('/tickets/bulk/delete', [TicketBulkController::class, 'destroy'])->middleware('throttle:5,1')->name('tickets.bulk.delete');
    Route::post('/tickets/bulk/force-delete', [TicketBulkController::class, 'forceDestroy'])->middleware('throttle:3,1')->name('tickets.bulk.force-delete');
    Route::post('/tickets/bulk/status', [TicketBulkController::class, 'updateStatus'])->middleware('throttle:5,1')->name('tickets.bulk.status');

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

    Route::get('/leads/trash', [LeadController::class, 'trash'])->name('leads.trash');
    Route::resource('leads', LeadController::class);
    Route::post('/leads/{lead}/restore', [LeadController::class, 'restore'])->name('leads.restore')->withTrashed();
    Route::delete('/leads/{lead}/force-delete', [LeadController::class, 'forceDestroy'])->name('leads.force-destroy')->withTrashed();

    Route::resource('lead-sources', LeadSourceController::class)->except(['show']);
    Route::post('/lead-sources/{leadSource}/run', [LeadSourceController::class, 'run'])->middleware('throttle:3,1')->name('lead-sources.run');

    Route::get('/clients/export', [ClientExportController::class, 'export'])->name('clients.export');
    Route::post('/clients/bulk/delete', [ClientBulkController::class, 'destroy'])->middleware('throttle:5,1')->name('clients.bulk.delete');
    Route::post('/clients/bulk/status', [ClientBulkController::class, 'updateStatus'])->middleware('throttle:5,1')->name('clients.bulk.status');
    Route::post('/clients/bulk/force-delete', [ClientBulkController::class, 'forceDestroy'])->middleware('throttle:3,1')->name('clients.bulk.force-delete');

    Route::get('/clients/trash', [ClientController::class, 'trash'])->name('clients.trash');
    Route::resource('clients', ClientController::class);
    Route::post('/clients/{client}/restore', [ClientController::class, 'restore'])->name('clients.restore')->withTrashed();
    Route::delete('/clients/{client}/force-delete', [ClientController::class, 'forceDestroy'])->name('clients.force-destroy')->withTrashed();
    Route::post('/clients/{client}/portal-link', [ClientController::class, 'generatePortalLink'])->name('clients.portal-link.generate');
    Route::get('/clients/{client}/portal-users', [PortalUserController::class, 'index'])->name('clients.portal-users.index');
    Route::post('/clients/{client}/portal-users', [PortalUserController::class, 'store'])->name('clients.portal-users.store');
    Route::delete('/clients/{client}/portal-users/{portalUser}', [PortalUserController::class, 'destroy'])->name('clients.portal-users.destroy');
    Route::post('/leads/{lead}/convert', [LeadConversionController::class, 'convert'])->name('leads.convert');

    Route::get('/projects/trash', [ProjectController::class, 'trash'])->name('projects.trash');
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/restore', [ProjectController::class, 'restore'])->name('projects.restore')->withTrashed();
    Route::delete('/projects/{project}/force-delete', [ProjectController::class, 'forceDestroy'])->name('projects.force-destroy')->withTrashed();
    Route::post('/projects/{project}/save-template', [ProjectController::class, 'saveAsTemplate'])->name('projects.save-template');
    Route::get('/api/clients', [ClientController::class, 'apiList'])->name('api.clients');
    Route::get('/api/project-templates', [ProjectController::class, 'templates'])->name('api.project-templates');
    Route::get('/api/project-templates/{project}/preview', [ProjectController::class, 'previewTemplate'])->name('api.project-templates.preview');
    Route::get('/projects/{project}/status-ping-preview', [ProjectController::class, 'statusPingPreview'])->name('projects.status-ping-preview');
    Route::post('/projects/{project}/send-status-ping', [ProjectController::class, 'sendStatusPing'])->middleware('throttle:5,1')->name('projects.send-status-ping');

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::put('/tasks/{task}/move', [TaskController::class, 'move'])->name('tasks.move');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::post('/trello/sync', [TrelloController::class, 'sync'])->name('trello.sync');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/api/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::get('/meetings/{meeting}/ics', [CalendarController::class, 'exportIcs'])->name('meetings.ics');

    Route::get('/meetings/upcoming', [MeetingController::class, 'upcoming'])->name('meetings.upcoming');
    Route::get('/{meetableType}/{meetable}/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::post('/{meetableType}/{meetable}/meetings', [MeetingController::class, 'storeForMeetable'])->name('meetings.store_for_meetable');
    Route::resource('meetings', MeetingController::class)->except(['index']);

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');

    Route::get('/api/search', [SearchController::class, 'index'])->name('api.search');
    Route::get('/api/entity-search', [TicketController::class, 'searchEntities'])->name('api.entity-search');

    Route::get('/maroni/clients/{client}/summary', [MaroniController::class, 'summary'])->name('maroni.clients.summary');
    Route::get('/maroni/clients/{client}/invoices', [MaroniController::class, 'invoices'])->name('maroni.clients.invoices');
    Route::get('/maroni/clients/{client}/expenses', [MaroniController::class, 'expenses'])->name('maroni.clients.expenses');
    Route::post('/maroni/sync-clients', [MaroniController::class, 'syncAll'])->name('maroni.sync');

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

    Route::get('/{commentableType}/{commentable}/comments', [CommentController::class, 'index'])->name('comments.index');
    Route::post('/{commentableType}/{commentable}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('/tickets/trash', [TicketController::class, 'trash'])->name('tickets.trash');
    Route::resource('tickets', TicketController::class);
    Route::post('/tickets/{ticket}/restore', [TicketController::class, 'restore'])->name('tickets.restore')->withTrashed();
    Route::delete('/tickets/{ticket}/force-delete', [TicketController::class, 'forceDestroy'])->name('tickets.force-destroy')->withTrashed();
    Route::post('/tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
    Route::post('/tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
    Route::post('/tickets/{ticket}/replies', [TicketReplyController::class, 'store'])->name('tickets.replies.store');

    Route::resource('templates', EmailTemplateController::class)->except(['show']);

    Route::resource('intake-forms', \App\Http\Controllers\IntakeFormController::class);
    Route::get('/intake-forms/{intakeForm}/submissions/{submission}', [\App\Http\Controllers\IntakeFormSubmissionController::class, 'show'])->name('intake-forms.submissions.show');
    Route::get('/intake-forms/submissions/download', [\App\Http\Controllers\IntakeFormSubmissionController::class, 'download'])->name('intake-forms.submissions.download');

});

Route::get('/review/{token}', [TestimonialController::class, 'show'])->name('review.show');
Route::post('/review/{token}', [TestimonialController::class, 'submit'])->name('review.submit');
Route::get('/review/{token}/thanks', [TestimonialController::class, 'thanks'])->name('review.thanks');

Route::middleware('auth')->group(function (): void {
    Route::get('/testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
    Route::delete('/testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');
    Route::post('/clients/{client}/request-review', [TestimonialController::class, 'requestFromClient'])->name('clients.request-review');
    Route::post('/projects/{project}/request-review', [TestimonialController::class, 'requestFromProject'])->name('projects.request-review');
});

require __DIR__.'/auth.php';
