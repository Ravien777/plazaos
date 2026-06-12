<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Client;
use App\Models\IntakeFormSubmission;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Project;
use App\Observers\ClientMaroniObserver;
use App\Observers\IntakeFormSubmissionObserver;
use App\Observers\LeadObserver;
use App\Observers\MeetingObserver;
use App\Observers\ProjectObserver;
use App\Models\User;
use App\Services\ActivityService;
use App\Services\BillingService;
use App\Services\MaroniService;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ActivityService::class, function () {
            return new ActivityService($this->app->make(WebhookService::class));
        });

        $this->app->singleton(MaroniService::class);
        $this->app->singleton(BillingService::class);
    }

    public function boot(): void
    {
        Lead::observe(LeadObserver::class);
        Meeting::observe(MeetingObserver::class);
        Project::observe(ProjectObserver::class);
        IntakeFormSubmission::observe(IntakeFormSubmissionObserver::class);
        Client::observe(ClientMaroniObserver::class);

        $this->overrideResendConfigFromSettings();

        Vite::prefetch(concurrency: 3);
    }

    private function overrideResendConfigFromSettings(): void
    {
        try {
            $user = User::first();
            $apiKey = $user?->getSetting('resend_api_key');

            if ($apiKey) {
                config(['services.resend.key' => $apiKey]);
            }
        } catch (\Exception) {
        }
    }
}
