<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Portal;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private ClientUser $clientUser;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['id' => 1]);

        $this->client = Client::factory()->create();
        $this->clientUser = ClientUser::factory()->create([
            'client_id' => $this->client->id,
            'email' => 'client@example.com',
        ]);
    }

    public function test_login_page_renders(): void
    {
        $response = $this->get(route('portal.login'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Portal/Auth/Login'));
    }

    public function test_client_can_login(): void
    {
        $response = $this->post(route('portal.login'), [
            'email' => 'client@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($this->clientUser, 'client');
    }

    public function test_client_cannot_login_with_invalid_password(): void
    {
        $response = $this->post(route('portal.login'), [
            'email' => 'client@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertInvalid(['email' => trans('auth.failed')]);
        $this->assertGuest('client');
    }

    public function test_client_can_logout(): void
    {
        $this->actingAs($this->clientUser, 'client');

        $response = $this->post(route('portal.logout'));

        $response->assertRedirect('/portal/login');
        $this->assertGuest('client');
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get(route('portal.dashboard'));

        $response->assertRedirect(route('portal.login'));
    }

    public function test_admin_login_does_not_affect_portal(): void
    {
        $admin = User::factory()->create(['id' => 999]);
        $this->actingAs($admin);

        $response = $this->get(route('portal.dashboard'));

        $response->assertRedirect(route('portal.login'));
    }
}
