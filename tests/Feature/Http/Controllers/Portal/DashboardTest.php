<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Portal;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Meeting;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['id' => 1]);

        $client = Client::factory()->create();
        $user = ClientUser::factory()->create(['client_id' => $client->id]);
        $this->actingAs($user, 'client');
    }

    public function test_dashboard_renders_with_stats(): void
    {
        $response = $this->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Portal/Dashboard')
            ->has('stats')
            ->has('recentTickets')
            ->has('upcomingMeetings')
        );
    }
}
