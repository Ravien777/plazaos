<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class NotificationPreferenceControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_index_returns_preferences(): void
    {
        $this->user->update([
            'notification_preferences' => ['slack_enabled' => true, 'digest_enabled' => false],
        ]);

        $this->actingAs($this->user)
            ->get(route('settings.notifications'))
            ->assertInertia(fn ($page) => $page
                ->component('Settings/Notifications')
                ->has('preferences')
                ->where('preferences.slack_enabled', true)
                ->where('preferences.digest_enabled', false)
            );
    }

    public function test_update_saves_slack_enabled(): void
    {
        $this->actingAs($this->user)
            ->put(route('settings.notifications'), [
                'slack_enabled' => true,
                'digest_enabled' => false,
            ])
            ->assertRedirect();

        $this->user->refresh();
        $this->assertTrue($this->user->notification_preferences['slack_enabled']);
        $this->assertFalse($this->user->notification_preferences['digest_enabled']);
    }

    public function test_update_saves_digest_with_time(): void
    {
        $this->actingAs($this->user)
            ->put(route('settings.notifications'), [
                'slack_enabled' => false,
                'digest_enabled' => true,
                'digest_time' => '07:30',
            ])
            ->assertRedirect();

        $this->user->refresh();
        $this->assertTrue($this->user->notification_preferences['digest_enabled']);
        $this->assertEquals('07:30', $this->user->notification_preferences['digest_time']);
    }

    public function test_update_validates_digest_time_format(): void
    {
        $this->actingAs($this->user)
            ->put(route('settings.notifications'), [
                'digest_enabled' => true,
                'digest_time' => 'invalid',
            ])
            ->assertSessionHasErrors('digest_time');
    }

    public function test_guest_redirects(): void
    {
        $this->get(route('settings.notifications'))
            ->assertRedirect(route('login'));
    }
}
