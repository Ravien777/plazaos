<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Portal;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalTokenControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_token_logs_in_and_redirects(): void
    {
        $client = Client::factory()->create([
            'portal_token' => (string) Str::uuid(),
            'portal_token_expires_at' => now()->addDays(7),
        ]);

        $response = $this->get(route('portal.token.login', $client->portal_token));

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertTrue(Auth::guard('client')->check());
        $this->assertEquals($client->email, Auth::guard('client')->user()->email);
    }

    public function test_valid_token_creates_client_user_if_none_exists(): void
    {
        $client = Client::factory()->create([
            'portal_token' => (string) Str::uuid(),
            'portal_token_expires_at' => now()->addDays(7),
        ]);

        $this->assertDatabaseMissing('client_users', ['client_id' => $client->id]);

        $this->get(route('portal.token.login', $client->portal_token));

        $this->assertDatabaseHas('client_users', ['client_id' => $client->id, 'email' => $client->email]);
    }

    public function test_expired_token_redirects_to_login_with_error(): void
    {
        $client = Client::factory()->create([
            'portal_token' => (string) Str::uuid(),
            'portal_token_expires_at' => now()->subDay(),
        ]);

        $response = $this->get(route('portal.token.login', $client->portal_token));

        $response->assertRedirect(route('portal.login'));
        $response->assertSessionHas('error');
        $this->assertFalse(Auth::guard('client')->check());
    }

    public function test_invalid_token_redirects_to_login_with_error(): void
    {
        $response = $this->get(route('portal.token.login', 'nonexistent-token'));

        $response->assertRedirect(route('portal.login'));
        $response->assertSessionHas('error');
        $this->assertFalse(Auth::guard('client')->check());
    }

    public function test_reuses_existing_client_user(): void
    {
        $client = Client::factory()->create([
            'portal_token' => (string) Str::uuid(),
            'portal_token_expires_at' => now()->addDays(7),
        ]);

        $existingUser = ClientUser::factory()->create([
            'client_id' => $client->id,
            'email' => $client->email,
        ]);

        $this->get(route('portal.token.login', $client->portal_token));

        $this->assertAuthenticatedAs($existingUser, 'client');
    }

    public function test_generate_portal_link_creates_token(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $client = Client::factory()->create();

        $response = $this->postJson(route('clients.portal-link.generate', $client));

        $response->assertOk();
        $response->assertJsonStructure(['portal_token', 'portal_token_expires_at', 'url']);
        $this->assertNotNull($client->fresh()->portal_token);
    }

    public function test_generate_portal_link_refreshes_expired_token(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $client = Client::factory()->create([
            'portal_token' => (string) Str::uuid(),
            'portal_token_expires_at' => now()->subDay(),
        ]);

        $oldToken = $client->fresh()->portal_token;

        $this->postJson(route('clients.portal-link.generate', $client));

        $this->assertNotEquals($oldToken, $client->fresh()->portal_token);
        $this->assertTrue($client->fresh()->portal_token_expires_at->isFuture());
    }
}
