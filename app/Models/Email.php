<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Email extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'emailable_type',
        'emailable_id',
        'from',
        'to',
        'subject',
        'body',
        'template',
        'template_data',
        'status',
        'external_id',
        'sent_at',
        'opened_at',
    ];

    protected function casts(): array
    {
        return [
            'template_data' => 'array',
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
        ];
    }

    public function emailable(): MorphTo
    {
        return $this->morphTo();
    }
}
