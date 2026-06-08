<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Actions\SyncTrelloAction;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

use function PHPUnit\Framework\assertContains;

class TrelloControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $team = Team::factory()->create();
        $user = User::factory()->create(['team_id' => $team->id]);
        $this->actingAs($user);
    }

    public function test_sync_success(): void
    {
        $mock = $this->mock(SyncTrelloAction::class);
        $mock->shouldReceive('execute')->andReturn(['created' => 1, 'updated' => 2, 'deleted' => 0]);

        $response = $this->post(route('trello.sync'));

        $response->assertSessionHas('success');
    }

    public function test_sync_returns_error_when_not_configured(): void
    {
        $mock = $this->mock(SyncTrelloAction::class);
        $mock->shouldReceive('execute')->andReturn(['error' => 'Trello not configured']);

        $response = $this->post(route('trello.sync'));

        $response->assertSessionHas('error');
    }

    public function test_sync_fails_without_team(): void
    {
        $user = User::factory()->create(['team_id' => null]);
        $this->actingAs($user);

        $response = $this->post(route('trello.sync'));

        $response->assertSessionHas('error');
    }

    public function test_guest_redirects(): void
    {
        $this->post(route('trello.sync'))
            ->assertStatus(302);
    }
}
