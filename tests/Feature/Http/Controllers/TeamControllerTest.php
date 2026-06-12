<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_returns_onboard_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('team.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Onboard/CreateTeam'));
    }

    public function test_create_redirects_if_user_has_team(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $user->update(['team_id' => $team->id, 'role' => 'owner']);

        $response = $this->actingAs($user)->get(route('team.create'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_store_creates_team_and_updates_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('team.store'), [
            'name' => 'Acme Corp',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('teams', ['name' => 'Acme Corp', 'owner_id' => $user->id]);

        $user->refresh();
        $this->assertNotNull($user->team_id);
        $this->assertEquals('owner', $user->role);
    }

    public function test_store_redirects_if_already_has_team(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $user->update(['team_id' => $team->id, 'role' => 'owner']);

        $response = $this->actingAs($user)->post(route('team.store'), [
            'name' => 'Another Team',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_store_validates_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('team.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_edit_returns_settings_page(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $user->update(['team_id' => $team->id, 'role' => 'owner']);

        $response = $this->actingAs($user)->get(route('team.edit'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Team/Settings')
            ->has('team')
        );
    }

    public function test_edit_redirects_if_no_team(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('team.edit'));

        $response->assertRedirect(route('team.create'));
    }

    public function test_edit_forbidden_for_member(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $owner->update(['team_id' => $team->id, 'role' => 'owner']);
        $member = User::factory()->create(['team_id' => $team->id, 'role' => 'member']);

        $response = $this->actingAs($member)->get(route('team.edit'));

        $response->assertForbidden();
    }

    public function test_update_changes_team_name(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id, 'name' => 'Old Name']);
        $user->update(['team_id' => $team->id, 'role' => 'owner']);

        $response = $this->actingAs($user)->put(route('team.update'), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect(route('team.edit'));
        $this->assertEquals('New Name', $team->fresh()->name);
    }

    public function test_update_forbidden_for_member(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $owner->update(['team_id' => $team->id, 'role' => 'owner']);
        $member = User::factory()->create(['team_id' => $team->id, 'role' => 'member']);

        $response = $this->actingAs($member)->put(route('team.update'), [
            'name' => 'Hacked Name',
        ]);

        $response->assertForbidden();
    }

    public function test_destroy_deletes_team(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $user->update(['team_id' => $team->id, 'role' => 'owner']);

        $response = $this->actingAs($user)->delete(route('team.destroy'));

        $response->assertRedirect(route('team.create'));
        $response->assertSessionHas('status');

        $this->assertSoftDeleted($team);
    }

    public function test_destroy_forbidden_for_member(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $owner->update(['team_id' => $team->id, 'role' => 'owner']);
        $member = User::factory()->create(['team_id' => $team->id, 'role' => 'member']);

        $response = $this->actingAs($member)->delete(route('team.destroy'));

        $response->assertForbidden();
    }

    public function test_destroy_clears_user_team(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $user->update(['team_id' => $team->id, 'role' => 'owner']);

        $this->actingAs($user)->delete(route('team.destroy'));

        $user->refresh();
        $this->assertNull($user->team_id);
    }
}
