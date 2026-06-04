<?php

declare(strict_types=1);

namespace App\Enums;

enum SourceType: string
{
    case LinkedIn = 'linkedin';
    case Upwork = 'upwork';
    case Freelancer = 'freelancer';
    case Fiverr = 'fiverr';
    case Guru = 'guru';
    case PeoplePerHour = 'people_per_hour';
    case Toptal = 'toptal';
    case AngelList = 'angel_list';
    case WellFound = 'wellfound';
    case RemoteOK = 'remote_ok';
    case WeWorkRemotely = 'we_work_remotely';
    case ColdEmail = 'cold_email';
    case Referral = 'referral';
    case Website = 'website';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::LinkedIn => 'LinkedIn',
            self::Upwork => 'Upwork',
            self::Freelancer => 'Freelancer',
            self::Fiverr => 'Fiverr',
            self::Guru => 'Guru',
            self::PeoplePerHour => 'PeoplePerHour',
            self::Toptal => 'Toptal',
            self::AngelList => 'AngelList',
            self::WellFound => 'Wellfound',
            self::RemoteOK => 'Remote OK',
            self::WeWorkRemotely => 'We Work Remotely',
            self::ColdEmail => 'Cold Email',
            self::Referral => 'Referral',
            self::Website => 'Website',
            self::Other => 'Other',
        };
    }
}
