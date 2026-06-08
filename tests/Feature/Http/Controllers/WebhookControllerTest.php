<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Models\Webhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_index_returns_webhooks_page(): void
    {
        Webhook::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('settings.webhooks'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Settings/Webhooks/Index')
            ->has('webhooks')
            ->has('allowedEvents')
        );
    }

    public function test_create_returns_create_page(): void
    {
        $response = $this->get(route('settings.webhooks.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Settings/Webhooks/Create')
            ->has('allowedEvents')
        );
    }

    public function test_store_creates_webhook(): void
    {
        $response = $this->post(route('settings.webhooks.store'), [
            'url' => 'https://hooks.zapier.com/123',
            'events' => ['lead.created', 'lead.converted'],
        ]);

        $response->assertRedirect(route('settings.webhooks'));
        $this->assertDatabaseHas('webhooks', [
            'user_id' => $this->user->id,
            'url' => 'https://hooks.zapier.com/123',
            'active' => true,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post(route('settings.webhooks.store'), []);

        $response->assertSessionHasErrors(['url', 'events']);
    }

    public function test_store_validates_event_values(): void
    {
        $response = $this->post(route('settings.webhooks.store'), [
            'url' => 'https://example.com/hook',
            'events' => ['invalid.event'],
        ]);

        $response->assertSessionHasErrors(['events.0']);
    }

    public function test_edit_returns_edit_page(): void
    {
        $webhook = Webhook::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('settings.webhooks.edit', $webhook));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Settings/Webhooks/Edit')
            ->has('webhook')
            ->has('allowedEvents')
        );
    }

    public function test_update_modifies_webhook(): void
    {
        $webhook = Webhook::factory()->create([
            'user_id' => $this->user->id,
            'url' => 'https://original.com/hook',
            'active' => true,
        ]);

        $response = $this->put(route('settings.webhooks.update', $webhook), [
            'url' => 'https://updated.com/hook',
            'events' => ['client.created'],
            'active' => false,
        ]);

        $response->assertRedirect(route('settings.webhooks'));
        $this->assertDatabaseHas('webhooks', [
            'id' => $webhook->id,
            'url' => 'https://updated.com/hook',
            'active' => false,
        ]);
    }

    public function test_destroy_deletes_webhook(): void
    {
        $webhook = Webhook::factory()->create(['user_id' => $this->user->id]);

        $response = $this->delete(route('settings.webhooks.destroy', $webhook));

        $response->assertRedirect(route('settings.webhooks'));
        $this->assertSoftDeleted('webhooks', ['id' => $webhook->id]);
    }

    public function test_test_dispatches_test_event(): void
    {
        $webhook = Webhook::factory()->create(['user_id' => $this->user->id]);

        $response = $this->post(route('settings.webhooks.test', $webhook));

        $response->assertRedirect(route('settings.webhooks'));
    }

    public function test_owner_can_delete_webhook(): void
    {
        $user = User::factory()->create(['id' => 2, 'team_id' => null]);
        $webhook = Webhook::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->delete(route('settings.webhooks.destroy', $webhook));

        $response->assertRedirect();
        $this->assertSoftDeleted('webhooks', ['id' => $webhook->id]);
    }

    public function test_guest_redirects_to_login(): void
    {
        auth()->logout();

        $response = $this->get(route('settings.webhooks'));

        $response->assertRedirect(route('login'));
    }
}
