<?php

declare(strict_types=1);

namespace App\LeadSources\Concerns;

use App\Enums\LeadStatus;
use App\Models\Lead;

trait DeduplicatesLeads
{
    private function createLead(array $item, string $sourceName): ?Lead
    {
        $email = $item['email'] ?? null;
        $contactName = $item['name'] ?? $item['contact_name'] ?? $item['full_name'] ?? null;
        $website = $item['website'] ?? null;
        $companyName = $item['company_name'] ?? $item['company'] ?? null;

        if ($email) {
            $exists = Lead::where('email', $email)->exists();
            if ($exists) {
                return null;
            }
        }

        if ($website && $companyName) {
            $exists = Lead::where('website', $website)
                ->where('company_name', $companyName)
                ->exists();
            if ($exists) {
                return null;
            }
        }

        if ($website && ! $email) {
            $exists = Lead::where('website', $website)->exists();
            if ($exists) {
                return null;
            }
        }

        if ($companyName && ! $email && ! $website) {
            $exists = Lead::where('company_name', $companyName)->exists();
            if ($exists) {
                return null;
            }
        }

        if (! $email && ! $contactName) {
            return null;
        }

        $data = [
            'contact_name' => $contactName,
            'source' => $sourceName,
            'status' => LeadStatus::New,
        ];

        if ($companyName) {
            $data['company_name'] = $companyName;
        }

        if ($email) {
            $data['email'] = $email;
        }

        foreach (['phone', 'website', 'industry', 'city', 'country'] as $field) {
            if (isset($item[$field])) {
                $data[$field] = $item[$field];
            }
        }

        return Lead::create($data);
    }
}
