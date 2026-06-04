<?php

declare(strict_types=1);

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:open,in_progress,waiting_client,closed'],
            'priority' => ['nullable', 'string', 'in:low,medium,high'],
            'category' => ['nullable', 'string', 'in:bug_report,feature_request,support,other'],
            'ticketable_type' => ['nullable', 'string', 'in:client,project'],
            'ticketable_id' => ['nullable', 'string', 'uuid'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->sometimes('ticketable_id', [
            'exists:clients,id',
        ], fn ($input) => ($input->ticketable_type ?? '') === 'client');

        $validator->sometimes('ticketable_id', [
            'exists:projects,id',
        ], fn ($input) => ($input->ticketable_type ?? '') === 'project');
    }
}
