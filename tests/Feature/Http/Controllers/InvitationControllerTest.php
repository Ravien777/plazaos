<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InvitationControllerTest extends TestCase
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

    public function test_show_returns_invite_page(): void
    {
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $response = $this->get(route('invitation.show', $invitation->token));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Invite/Show')
            ->has('token')
            ->has('team')
            ->has('email')
        );
    }

    public function test_show_redirects_if_already_accepted(): void
    {
        $invitation = TeamInvitation::factory()->accepted()->create([
            'team_id' => $this->team->id,
        ]);

        $response = $this->get(route('invitation.show', $invitation->token));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'This invitation has already been accepted.');
    }

    public function test_show_renders_expired_page(): void
    {
        $invitation = TeamInvitation::factory()->expired()->create([
            'team_id' => $this->team->id,
        ]);

        $response = $this->get(route('invitation.show', $invitation->token));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Invite/Expired'));
    }

    public function test_accept_creates_user_and_logs_in(): void
    {
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $this->team->id,
            'email' => 'newuser@example.com',
        ]);

        $response = $this->post(route('invitation.accept', $invitation->token), [
            'name' => 'New User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'team_id' => $this->team->id,
            'role' => 'member',
        ]);
        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->assertAuthenticated();
    }

    public function test_accept_adds_existing_user_to_team(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'team_id' => null,
            'role' => null,
        ]);
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $this->team->id,
            'email' => 'existing@example.com',
        ]);

        $response = $this->post(route('invitation.accept', $invitation->token), [
            'name' => 'Any Name',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $existingUser->refresh();
        $this->assertEquals($this->team->id, $existingUser->team_id);
        $this->assertEquals('member', $existingUser->role);
        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->assertAuthenticatedAs($existingUser);
    }

    public function test_accept_redirects_if_already_accepted(): void
    {
        $invitation = TeamInvitation::factory()->accepted()->create([
            'team_id' => $this->team->id,
        ]);

        $response = $this->post(route('invitation.accept', $invitation->token), [
            'name' => 'Test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_accept_redirects_if_expired(): void
    {
        $invitation = TeamInvitation::factory()->expired()->create([
            'team_id' => $this->team->id,
        ]);

        $response = $this->post(route('invitation.accept', $invitation->token), [
            'name' => 'Test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_accept_validates_required_fields(): void
    {
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $response = $this->post(route('invitation.accept', $invitation->token), []);

        $response->assertSessionHasErrors(['name', 'password']);
    }

    public function test_store_creates_invitation_and_sends_notification(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->owner)->post(route('team.members.invite'), [
            'email' => 'invited@example.com',
        ]);

        $response->assertRedirect(route('team.edit'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('team_invitations', [
            'team_id' => $this->team->id,
            'email' => 'invited@example.com',
            'accepted_at' => null,
        ]);

        Notification::assertSentTo(
            new \Illuminate\Notifications\AnonymousNotifiable,
            TeamInvitationNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routeNotificationFor('mail') === 'invited@example.com';
            }
        );
    }

    public function test_store_requires_owner_role(): void
    {
        $member = User::factory()->create(['team_id' => $this->team->id, 'role' => 'member']);

        $response = $this->actingAs($member)->post(route('team.members.invite'), [
            'email' => 'invited@example.com',
        ]);

        $response->assertForbidden();
    }

    public function test_store_validates_email(): void
    {
        $response = $this->actingAs($this->owner)->post(route('team.members.invite'), [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_store_rejects_duplicate_invitation(): void
    {
        TeamInvitation::factory()->create([
            'team_id' => $this->team->id,
            'email' => 'invited@example.com',
        ]);

        $response = $this->actingAs($this->owner)->post(route('team.members.invite'), [
            'email' => 'invited@example.com',
        ]);

        $response->assertRedirect(route('team.edit'));
        $response->assertSessionHas('error');
    }

    public function test_store_rejects_when_team_full(): void
    {
        User::factory()->count(19)->create([
            'team_id' => $this->team->id,
        ]);

        $response = $this->actingAs($this->owner)->post(route('team.members.invite'), [
            'email' => 'extra@example.com',
        ]);

        $response->assertRedirect(route('team.edit'));
        $response->assertSessionHas('error');
    }

    public function test_destroy_cancels_invitation(): void
    {
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $response = $this->actingAs($this->owner)->delete(route('team.invitations.destroy', $invitation));

        $response->assertRedirect(route('team.edit'));
        $response->assertSessionHas('status');
        $this->assertSoftDeleted($invitation);
    }

    public function test_destroy_forbidden_for_member(): void
    {
        $member = User::factory()->create(['team_id' => $this->team->id, 'role' => 'member']);
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $response = $this->actingAs($member)->delete(route('team.invitations.destroy', $invitation));

        $response->assertForbidden();
    }
}
