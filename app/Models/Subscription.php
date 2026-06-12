<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'team_id',
        'plan_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'status',
        'trial_ends_at',
        'current_period_ends_at',
        'canceled_at',
        'ended_at',
        'seats',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isTrialing(): bool
    {
        return $this->status === SubscriptionStatus::Trialing->value;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [
            SubscriptionStatus::Active->value,
            SubscriptionStatus::Trialing->value,
            SubscriptionStatus::PastDue->value,
        ]);
    }

    public function isCanceled(): bool
    {
        return $this->status === SubscriptionStatus::Canceled->value;
    }

    public function onTrial(): bool
    {
        return $this->isTrialing() && $this->trial_ends_at?->isFuture();
    }

    public function trialDaysRemaining(): int
    {
        if (!$this->trial_ends_at) {
            return 0;
        }

        return (int) max(0, now()->startOfDay()->diffInDays($this->trial_ends_at->startOfDay()));
    }

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'canceled_at' => 'datetime',
            'ended_at' => 'datetime',
            'seats' => 'integer',
        ];
    }
}
