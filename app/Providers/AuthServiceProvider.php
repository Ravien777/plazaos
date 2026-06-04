<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Client;
use App\Models\Document;
use App\Models\Email;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\LeadImport;
use App\Models\LeadSource;
use App\Models\Meeting;
use App\Models\Note;
use App\Models\Project;
use App\Policies\ClientPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\EmailPolicy;
use App\Policies\EmailTemplatePolicy;
use App\Policies\LeadImportPolicy;
use App\Policies\LeadPolicy;
use App\Policies\LeadSourcePolicy;
use App\Policies\MeetingPolicy;
use App\Policies\NotePolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Lead::class => LeadPolicy::class,
        Note::class => NotePolicy::class,
        Client::class => ClientPolicy::class,
        Project::class => ProjectPolicy::class,
        Meeting::class => MeetingPolicy::class,
        LeadSource::class => LeadSourcePolicy::class,
        Document::class => DocumentPolicy::class,
        Email::class => EmailPolicy::class,
        EmailTemplate::class => EmailTemplatePolicy::class,
        LeadImport::class => LeadImportPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
