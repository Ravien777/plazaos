<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Qualified = 'qualified';
    case Contacted = 'contacted';
    case Interested = 'interested';
    case MeetingScheduled = 'meeting_scheduled';
    case ProposalSent = 'proposal_sent';
    case Won = 'won';
    case Lost = 'lost';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Qualified => 'Qualified',
            self::Contacted => 'Contacted',
            self::Interested => 'Interested',
            self::MeetingScheduled => 'Meeting Scheduled',
            self::ProposalSent => 'Proposal Sent',
            self::Won => 'Won',
            self::Lost => 'Lost',
            self::Archived => 'Archived',
        };
    }
}
