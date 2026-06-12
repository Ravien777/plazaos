<?php

declare(strict_types=1);

use App\Console\Commands\CheckInactiveLeads;
use App\Console\Commands\HandleExpiredTrials;
use App\Console\Commands\ScrapeLeadSources;
use App\Console\Commands\SendDigestEmails;
use App\Console\Commands\SendMeetingReminders;
use App\Console\Commands\SyncClientsToMaroni;
use App\Console\Commands\SyncTrello;
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
Schedule::command(SyncTrello::class)->everyFifteenMinutes();
Schedule::command(SyncClientsToMaroni::class, ['--new'])->everyFiveMinutes();
Schedule::command(SyncClientsToMaroni::class)->daily();
Schedule::command(HandleExpiredTrials::class)->daily();
