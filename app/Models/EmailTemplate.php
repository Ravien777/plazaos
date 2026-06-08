<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use BelongsToTeam, HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'key',
        'name',
        'subject',
        'body',
        'variables',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
        ];
    }
}
