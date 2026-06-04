<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Client;
use App\Models\Project;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Support\Str;

class RequestTestimonialAction
{
    public function __construct(
        private readonly EmailService $emailService
    ) {}

    public function execute(Client $client, ?Project $project, User $sender): Testimonial
    {
        $testimonial = Testimonial::create([
            'client_id' => $client->id,
            'project_id' => $project?->id,
            'rating' => 0,
            'review_token' => (string) Str::uuid(),
            'is_approved' => false,
        ]);

        $senderCompany = $sender->team?->name ?? $sender->name;

        $this->emailService->sendFromTemplate($client, 'testimonial_request', [
            'contact_name' => $client->contact_name,
            'company_name' => $client->company_name,
            'sender_name' => $sender->name,
            'sender_company' => $senderCompany,
            'review_url' => url('/review/' . $testimonial->review_token),
        ]);

        activity()->log($client, 'testimonial.requested', "Testimonial requested from {$client->company_name}" . ($project ? " for project {$project->name}" : ''));

        return $testimonial;
    }
}
