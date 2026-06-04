<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendDigestEmailsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_sends_to_enabled_users(): void
    {
        Mail::fake();

        User::factory()->create([
            'notification_preferences' => ['digest_enabled' => true],
        ]);

        $this->artisan('plazaos:send-digests')
            ->assertSuccessful();

        Mail::assertSent(\App\Mail\DailyDigest::class);
    }

    public function test_skips_disabled_users(): void
    {
        Mail::fake();

        User::factory()->create([
            'notification_preferences' => ['digest_enabled' => false],
        ]);

        User::factory()->create([
            'notification_preferences' => null,
        ]);

        $this->artisan('plazaos:send-digests')
            ->assertSuccessful();

        Mail::assertNothingSent();
    }
}
