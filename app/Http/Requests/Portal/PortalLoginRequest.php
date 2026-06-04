<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class PortalLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (!Auth::guard('client')->attempt($this->only('email', 'password'), (bool) $this->boolean('remember'))) {
            $this->rateLimit();

            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $this->clearRateLimit();
    }

    protected function throttleKey(): string
    {
        return 'portal-login:' . Str::lower($this->input('email'));
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => 60]),
            ]);
        }
    }

    protected function rateLimit(): void
    {
        RateLimiter::hit($this->throttleKey(), 60);
    }

    protected function clearRateLimit(): void
    {
        RateLimiter::clear($this->throttleKey());
    }
}
