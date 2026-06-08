<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_index_returns_200(): void
    {
        $user = auth()->user();
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Test notification.'],
        ]);

        $response = $this->get(route('notifications.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Notifications/Index'));
    }

    public function test_unread_count(): void
    {
        $user = auth()->user();
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Unread 1.'],
        ]);

        $response = $this->get(route('notifications.unread-count'));

        $response->assertOk();
        $response->assertJson(['count' => 1]);
    }

    public function test_recent_returns_unread(): void
    {
        $user = auth()->user();
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Unread 1.'],
        ]);
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Unread 2.'],
        ]);
        $readNotification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Read.'],
        ]);
        $readNotification->markAsRead();

        $response = $this->get(route('notifications.recent'));

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_mark_as_read(): void
    {
        $user = auth()->user();
        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Mark me read.'],
        ]);

        $response = $this->post(route('notifications.mark-as-read', $notification->id));

        $response->assertOk();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read(): void
    {
        $user = auth()->user();
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'One.'],
        ]);
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Two.'],
        ]);
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Three.'],
        ]);

        $response = $this->post(route('notifications.mark-all-as-read'));

        $response->assertOk();
        $this->assertEquals(0, $user->fresh()->unreadNotifications->count());
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }
}
