<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Portal;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingControllerTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private Client $otherClient;
    private ClientUser $user;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();

        $this->client = Client::factory()->create();
        $this->otherClient = Client::factory()->create();
        $this->user = ClientUser::factory()->create(['client_id' => $this->client->id]);
        $this->actingAs($this->user, 'client');
    }

    public function test_index_shows_only_own_meetings(): void
    {
        Meeting::factory()->create([
            'meetable_type' => 'client',
            'meetable_id' => $this->client->id,
            'title' => 'Our Meeting',
            'start_time' => now()->addDay(),
        ]);
        Meeting::factory()->create([
            'meetable_type' => 'client',
            'meetable_id' => $this->otherClient->id,
            'title' => 'Their Meeting',
            'start_time' => now()->addDay(),
        ]);

        $response = $this->get(route('portal.meetings.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Portal/Meetings/Index')
            ->has('meetings.data', 1)
            ->where('meetings.data.0.title', 'Our Meeting')
        );
    }
}
