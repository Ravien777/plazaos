<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadSource extends Model
{
    use HasFactory, HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'config',
        'is_active',
        'frequency',
        'last_run_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => SourceType::class,
            'config' => 'array',
            'is_active' => 'boolean',
            'frequency' => 'string',
            'last_run_at' => 'datetime',
        ];
    }
}
