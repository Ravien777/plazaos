<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTeam
{
    public static function bootBelongsToTeam(): void
    {
        static::addGlobalScope('team', function (Builder $query) {
            if ($teamId = auth()->user()?->team_id) {
                $query->where($query->getModel()->qualifyColumn('team_id'), $teamId);
            }
        });

        static::creating(function ($model) {
            if (!$model->team_id && $teamId = auth()->user()?->team_id) {
                $model->team_id = $teamId;
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
