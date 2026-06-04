<?php

declare(strict_types=1);

namespace App\Http\Requests\LeadSource;

use App\Enums\SourceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateLeadSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(SourceType::class)],
            'config' => ['nullable', 'json'],
            'is_active' => ['boolean'],
            'frequency' => ['required', 'string', 'in:manual,hourly,daily,weekly'],
        ];
    }
}
