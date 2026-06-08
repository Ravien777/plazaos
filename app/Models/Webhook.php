<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Webhook extends Model
{
    use BelongsToTeam, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'team_id',
        'user_id',
        'url',
        'events',
        'secret',
        'active',
        'last_sent_at',
        'last_error_at',
        'last_error_message',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'active' => 'boolean',
            'last_sent_at' => 'datetime',
            'last_error_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function allowedEvents(): array
    {
        return [
            'lead.created' => 'Lead Created',
            'lead.converted' => 'Lead Converted',
            'client.created' => 'Client Created',
            'project.created' => 'Project Created',
            'project.completed' => 'Project Completed',
            'meeting.scheduled' => 'Meeting Scheduled',
            'ticket.created' => 'Ticket Created',
            'ticket.closed' => 'Ticket Closed',
            'document.uploaded' => 'Document Uploaded',
            'email.opened' => 'Email Opened',
        ];
    }
}
