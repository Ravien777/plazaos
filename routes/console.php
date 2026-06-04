<?php

declare(strict_types=1);

use App\Console\Commands\CheckInactiveLeads;
use App\Console\Commands\ScrapeLeadSources;
use App\Console\Commands\SendDigestEmails;
use App\Console\Commands\SendMeetingReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ScrapeLeadSources::class)->hourly();
Schedule::command(SendMeetingReminders::class)->everyMinute();
Schedule::command(CheckInactiveLeads::class)->daily();
Schedule::command(SendDigestEmails::class)->dailyAt('08:00');
