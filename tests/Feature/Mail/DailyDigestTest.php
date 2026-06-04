<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Mail\DailyDigest;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DailyDigestTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_digest_renders(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);

        $mailable = new DailyDigest($user);

        $mailable->assertSeeInHtml('Test User');
        $mailable->assertSeeInHtml('Daily');
        $mailable->assertSeeInHtml('Open Dashboard');
    }
}
