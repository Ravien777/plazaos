<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;

class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable, PasskeyAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'team_id',
        'role',
        'avatar',
        'notification_preferences',
        'dashboard_layout',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(UserSetting::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = $this->settings->firstWhere('key', $key);

        return $setting?->value ?? $default;
    }

    public function routeNotificationForSlack(object $notification): string
    {
        return config('services.slack.notifications.channel', '#general');
    }

    public function slackEnabled(): bool
    {
        return $this->notification_preferences['slack_enabled'] ?? false;
    }

    public function digestEnabled(): bool
    {
        return $this->notification_preferences['digest_enabled'] ?? false;
    }

    public function digestTime(): string
    {
        return $this->notification_preferences['digest_time'] ?? '08:00';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function canDelete(): bool
    {
        return $this->team_id === null || $this->isOwner();
    }

    public function completeOnboardingStep(string $step): void
    {
        $data = $this->onboarding_data ?? $this->defaultOnboardingData();
        $data['steps'][$step] = true;
        if (collect($data['steps'])->every(fn ($v) => $v === true)) {
            $data['completed'] = true;
        }
        $this->onboarding_data = $data;
        $this->save();
    }

    public function needsOnboarding(): bool
    {
        $data = $this->onboarding_data ?? $this->defaultOnboardingData();

        return !$data['completed'] && !$data['skipped'] && !$data['dismissed'];
    }

    public function defaultOnboardingData(): array
    {
        return [
            'completed' => false,
            'skipped' => false,
            'dismissed' => false,
            'wizard_seen' => false,
            'steps' => [
                'profile' => ($this->name !== null && $this->name !== ''),
                'team' => $this->team_id !== null,
                'first_lead' => false,
                'first_project' => false,
                'integrations' => false,
            ],
        ];
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_preferences' => 'array',
            'dashboard_layout' => 'array',
            'onboarding_data' => 'array',
        ];
    }
}
