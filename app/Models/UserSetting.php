<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use BelongsToTeam;

    public $timestamps = false;

    protected $fillable = [
        'team_id',
        'user_id',
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
