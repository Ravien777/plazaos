<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\IntakeFormSubmission;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Project;
use App\Observers\IntakeFormSubmissionObserver;
use App\Observers\LeadObserver;
use App\Observers\MeetingObserver;
use App\Observers\ProjectObserver;
use App\Services\ActivityService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ActivityService::class, function () {
            return new ActivityService;
        });
    }

    public function boot(): void
    {
        Lead::observe(LeadObserver::class);
        Meeting::observe(MeetingObserver::class);
        Project::observe(ProjectObserver::class);
        IntakeFormSubmission::observe(IntakeFormSubmissionObserver::class);

        Vite::prefetch(concurrency: 3);
    }
}
