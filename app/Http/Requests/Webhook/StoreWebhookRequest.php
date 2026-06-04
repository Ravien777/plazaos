<?php

declare(strict_types=1);

namespace App\Http\Requests\Webhook;

use App\Models\Webhook;
use Illuminate\Foundation\Http\FormRequest;

class StoreWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowed = array_keys(Webhook::allowedEvents());

        return [
            'url' => ['required', 'url'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['required', 'string', 'in:' . implode(',', $allowed)],
        ];
    }
}
