<?php

declare(strict_types=1);

namespace App\Http\Requests\Meeting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_time' => ['sometimes', 'required', 'date'],
            'end_time' => ['nullable', 'date', 'after:start_time'],
            'location' => ['nullable', 'string', 'max:255'],
            'meet_link' => ['nullable', 'url', 'max:255'],
            'provider' => ['nullable', 'string', 'in:google_meet,zoom,microsoft_teams'],
            'status' => ['nullable', 'string', 'in:scheduled,completed,cancelled'],
        ];
    }
}
