<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_index_returns_200_with_stats(): void
    {
        Lead::factory()->count(3)->create(['status' => 'new']);
        Client::factory()->count(2)->create(['status' => 'active']);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats')
            ->has('recentActivities')
        );
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
