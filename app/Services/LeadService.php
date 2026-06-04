<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadCreated;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Notification;

class LeadService
{
    private const ALLOWED_SORT_FIELDS = [
        'company_name', 'contact_name', 'email', 'website', 'industry',
        'city', 'country', 'source', 'status', 'created_at', 'updated_at',
        'last_contacted_at',
    ];

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Lead::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        $sortField = $this->resolveSortField($filters['sort_field'] ?? null);
        $sortDirection = $this->resolveSortDirection($filters['sort_direction'] ?? null);

        return $query->orderBy($sortField, $sortDirection)->paginate(25);
    }

    public function create(array $data): Lead
    {
        $lead = Lead::create($data);

        activity()->log($lead, 'lead.created', "Lead {$lead->company_name} was created.");
        Notification::send(User::first(), new LeadCreated($lead));

        return $lead;
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);

        activity()->log($lead, 'lead.updated', "Lead {$lead->company_name} was updated.");

        return $lead;
    }

    public function delete(Lead $lead): void
    {
        activity()->log($lead, 'lead.deleted', "Lead {$lead->company_name} was deleted.");

        $lead->delete();
    }

    public function bulkDelete(array $ids): void
    {
        $leads = Lead::whereIn('id', $ids)->get();

        foreach ($leads as $lead) {
            activity()->log($lead, 'lead.deleted', "Lead {$lead->company_name} was deleted (bulk).");
        }

        Lead::whereIn('id', $ids)->delete();
    }

    public function bulkDeleteByFilters(array $filters): int
    {
        $query = Lead::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        $ids = $query->pluck('id');

        if ($ids->isEmpty()) {
            return 0;
        }

        $leads = Lead::whereIn('id', $ids)->get();

        foreach ($leads as $lead) {
            activity()->log($lead, 'lead.deleted', "Lead {$lead->company_name} was deleted (filter bulk).");
        }

        return Lead::whereIn('id', $ids)->delete();
    }

    public function bulkUpdateStatus(array $ids, string $status): void
    {
        $leads = Lead::whereIn('id', $ids)->get();

        Lead::whereIn('id', $ids)->update(['status' => $status]);

        foreach ($leads as $lead) {
            activity()->log($lead, 'lead.updated', "Lead {$lead->company_name} status changed to {$status} (bulk).");
        }
    }

    private function resolveSortField(?string $field): string
    {
        if ($field && in_array($field, self::ALLOWED_SORT_FIELDS, true)) {
            return $field;
        }

        return 'created_at';
    }

    private function resolveSortDirection(?string $direction): string
    {
        return strtolower($direction ?? '') === 'asc' ? 'asc' : 'desc';
    }
}
