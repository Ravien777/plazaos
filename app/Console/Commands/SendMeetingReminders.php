<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Models\User;
use App\Notifications\MeetingReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendMeetingReminders extends Command
{
    protected $signature = 'meetings:send-reminders';

    protected $description = 'Send reminders for meetings starting within the next hour.';

    public function handle(): int
    {
        $meetings = Meeting::where('start_time', '>', now())
            ->where('start_time', '<', now()->addHour())
            ->where('reminder_sent', false)
            ->get();

        if ($meetings->isEmpty()) {
            $this->info('No meetings to remind about.');

            return self::SUCCESS;
        }

        foreach ($meetings as $meeting) {
            $meeting->loadMissing('meetable');

            Notification::send(User::all(), new MeetingReminder($meeting));

            $meeting->update(['reminder_sent' => true]);

            $this->info("Reminder sent for meeting: {$meeting->title}");
        }

        return self::SUCCESS;
    }
}
