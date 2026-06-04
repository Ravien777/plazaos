<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadConverted;
use App\Services\AutomationService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConvertLeadToClientAction
{
    public function execute(Lead $lead): Client
    {
        $client = Client::create([
            'company_name' => $lead->company_name,
            'contact_name' => $lead->contact_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'website' => $lead->website,
            'industry' => $lead->industry,
            'city' => $lead->city,
            'country' => $lead->country,
            'source' => $lead->source,
            'notes' => $lead->notes,
            'lead_id' => $lead->id,
            'last_contacted_at' => $lead->last_contacted_at,
        ]);

        $lead->update([
            'status' => LeadStatus::Archived,
            'converted_at' => now(),
        ]);

        foreach ($lead->notes()->get() as $note) {
            $client->notes()->create($note->only('content', 'created_by'));
        }

        foreach ($lead->emails()->get() as $email) {
            $client->emails()->create(
                $email->only('from', 'to', 'subject', 'body', 'template', 'template_data', 'status', 'external_id', 'sent_at')
            );
        }

        $disk = config('filesystems.default') === 'r2' ? 'r2' : 'local';

        foreach ($lead->documents()->get() as $document) {
            $newPath = $document->path;

            if ($document->path && Storage::disk($disk)->exists($document->path)) {
                $extension = pathinfo($document->path, PATHINFO_EXTENSION);
                $newPath = 'clients/' . $client->id . '/' . Str::uuid() . '.' . $extension;
                Storage::disk($disk)->copy($document->path, $newPath);
            }

            $client->documents()->create(
                array_merge(
                    $document->only('name', 'mime_type', 'size', 'user_id'),
                    ['path' => $newPath]
                )
            );
        }

        activity()->log($lead, 'lead.converted', "Lead {$lead->company_name} was converted to client.");
        activity()->log($client, 'client.created_from_lead', "Client {$client->company_name} was created from lead.");
        Notification::send(User::all(), new LeadConverted($lead, $client));

        app(AutomationService::class)->onLeadConverted($lead, $client);

        return $client;
    }
}
