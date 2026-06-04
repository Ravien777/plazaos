<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalUserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['id' => 1]);
        $this->actingAs($user);
    }

    public function test_index_returns_portal_users(): void
    {
        $client = Client::factory()->create();
        ClientUser::factory()->create([
            'client_id' => $client->id,
            'name' => 'Portal User',
        ]);

        $response = $this->get(route('clients.portal-users.index', $client));

        $response->assertOk();
        $response->assertJson(fn ($json) => $json
            ->has('data', 1)
            ->where('data.0.name', 'Portal User')
        );
    }

    public function test_store_creates_portal_user(): void
    {
        $client = Client::factory()->create();

        $response = $this->post(route('clients.portal-users.store', $client), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('client_users', [
            'client_id' => $client->id,
            'name' => 'New User',
            'email' => 'new@example.com',
        ]);
    }

    public function test_store_validates_email_unique(): void
    {
        $client = Client::factory()->create();
        ClientUser::factory()->create([
            'client_id' => $client->id,
            'email' => 'existing@example.com',
        ]);

        $response = $this->post(route('clients.portal-users.store', $client), [
            'name' => 'Duplicate',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_destroy_deletes_portal_user(): void
    {
        $client = Client::factory()->create();
        $portalUser = ClientUser::factory()->create(['client_id' => $client->id]);

        $response = $this->delete(route('clients.portal-users.destroy', [$client, $portalUser]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('client_users', ['id' => $portalUser->id]);
    }
}
