<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'template_name')
                    ->where('is_template', true)
                    ->where('team_id', auth()->user()->team_id),
            ],
        ];
    }
}
