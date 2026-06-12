<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create();
        $this->team = Team::factory()->create(['owner_id' => $this->owner->id]);
        $this->owner->update(['team_id' => $this->team->id, 'role' => 'owner']);
    }

    public function test_index_shows_members(): void
    {
        $member = User::factory()->create(['team_id' => $this->team->id, 'role' => 'member']);

        $response = $this->actingAs($this->owner)->get(route('team.members'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Team/Members')
            ->has('members', 2)
            ->where('isOwner', true)
        );
    }

    public function test_index_redirects_if_no_team(): void
    {
        $solo = User::factory()->create();

        $response = $this->actingAs($solo)->get(route('team.members'));

        $response->assertRedirect(route('team.create'));
    }

    public function test_destroy_removes_member_from_team(): void
    {
        $member = User::factory()->create(['team_id' => $this->team->id, 'role' => 'member']);

        $response = $this->actingAs($this->owner)->delete(route('team.members.remove', $member));

        $response->assertRedirect(route('team.members'));
        $response->assertSessionHas('status');

        $member->refresh();
        $this->assertNull($member->team_id);
        $this->assertEquals('member', $member->role);
    }

    public function test_destroy_forbidden_for_member(): void
    {
        $member = User::factory()->create(['team_id' => $this->team->id, 'role' => 'member']);

        $response = $this->actingAs($member)->delete(route('team.members.remove', $this->owner));

        $response->assertForbidden();
    }

    public function test_destroy_cannot_remove_self(): void
    {
        $response = $this->actingAs($this->owner)->delete(route('team.members.remove', $this->owner));

        $response->assertRedirect(route('team.members'));
        $response->assertSessionHas('error');
    }

    public function test_leave_removes_member_from_team(): void
    {
        $member = User::factory()->create(['team_id' => $this->team->id, 'role' => 'member']);

        $response = $this->actingAs($member)->post(route('team.leave'));

        $response->assertRedirect(route('team.create'));
        $response->assertSessionHas('status');

        $member->refresh();
        $this->assertNull($member->team_id);
    }

    public function test_owner_cannot_leave(): void
    {
        $response = $this->actingAs($this->owner)->post(route('team.leave'));

        $response->assertRedirect(route('team.members'));
        $response->assertSessionHas('error');

        $this->owner->refresh();
        $this->assertNotNull($this->owner->team_id);
    }

    public function test_leave_redirects_if_no_team(): void
    {
        $solo = User::factory()->create();

        $response = $this->actingAs($solo)->post(route('team.leave'));

        $response->assertRedirect(route('team.create'));
    }
}
