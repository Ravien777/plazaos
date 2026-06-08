<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_edit_returns_200(): void
    {
        $response = $this->get(route('settings.integrations'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Settings/Integrations')
            ->has('settings')
        );
    }

    public function test_update_saves_setting(): void
    {
        $response = $this->post(route('settings.integrations'), [
            'zoom_enabled' => 'true',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('user_settings', [
            'user_id' => auth()->id(),
            'key' => 'zoom_enabled',
            'value' => 'true',
        ]);
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->get(route('settings.integrations'))->assertRedirect(route('login'));
    }
}
