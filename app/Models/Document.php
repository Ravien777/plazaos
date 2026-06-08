<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use BelongsToTeam, HasFactory, HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'documentable_type',
        'documentable_id',
        'name',
        'path',
        'mime_type',
        'size',
        'user_id',
    ];

    protected $appends = [
        'signed_download_url',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getSignedDownloadUrlAttribute(): string
    {
        if (config('filesystems.default') === 'r2') {
            return Storage::disk('r2')->temporaryUrl($this->path, now()->addHour());
        }

        return route('documents.download', $this);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
