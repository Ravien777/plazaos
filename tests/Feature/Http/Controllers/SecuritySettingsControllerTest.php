<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecuritySettingsControllerTest extends TestCase
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
        $response = $this->get(route('settings.security'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Settings/Security')
            ->has('twoFactor')
            ->has('passkeys')
        );
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->get(route('settings.security'))->assertRedirect(route('login'));
    }
}
