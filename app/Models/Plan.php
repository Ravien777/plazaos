<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'stripe_price_id',
        'slug',
        'name',
        'description',
        'monthly_price_cents',
        'max_users',
        'features',
        'is_active',
        'sort_order',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isFree(): bool
    {
        return $this->monthly_price_cents === 0;
    }

    public function isPaid(): bool
    {
        return $this->monthly_price_cents > 0;
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    public function priceInDollars(): string
    {
        return number_format($this->monthly_price_cents / 100, 2);
    }

    protected function casts(): array
    {
        return [
            'monthly_price_cents' => 'integer',
            'max_users' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
