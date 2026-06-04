<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\DailyDigest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDigestEmails extends Command
{
    protected $signature = 'plazaos:send-digests';

    protected $description = 'Send daily digest emails to users who have enabled them.';

    public function handle(): int
    {
        $users = User::where('notification_preferences->digest_enabled', true)->get();

        if ($users->isEmpty()) {
            $this->info('No users have digests enabled.');

            return self::SUCCESS;
        }

        foreach ($users as $user) {
            Mail::to($user)->send(new DailyDigest($user));
            $this->info("Digest sent to {$user->email}");
        }

        return self::SUCCESS;
    }
}
