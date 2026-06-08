<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $user;
    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $team = Team::factory()->create();

        $this->user = User::factory()->create([
            'team_id' => $team->id,
            'role' => 'owner',
        ]);

        $this->member = User::factory()->create([
            'team_id' => $team->id,
            'role' => 'member',
        ]);
    }

    public function test_index_returns_board(): void
    {
        Task::factory()->count(3)->create(['created_by' => $this->user->id]);

        $this->actingAs($this->user)
            ->get(route('tasks.index'))
            ->assertInertia(fn ($page) => $page->component('Tasks/Index'));
    }

    public function test_store_creates_task(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->post(route('tasks.store'), [
                'title' => 'Test task',
                'project_id' => $project->id,
                'status' => 'todo',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test task',
            'project_id' => $project->id,
            'status' => 'todo',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_store_validates_title(): void
    {
        $this->actingAs($this->user)
            ->post(route('tasks.store'), [
                'title' => '',
                'status' => 'todo',
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_store_auto_orders_last(): void
    {
        Task::factory()->create(['status' => TaskStatus::Todo, 'order' => 5, 'created_by' => $this->user->id, 'team_id' => $this->user->team_id]);

        $this->actingAs($this->user)
            ->post(route('tasks.store'), [
                'title' => 'New task',
                'status' => 'todo',
            ]);

        $newTask = Task::where('title', 'New task')->first();
        $this->assertEquals(6, $newTask->order);
    }

    public function test_update_changes_fields(): void
    {
        $task = Task::factory()->create([
            'title' => 'Old title',
            'priority' => 'low',
            'created_by' => $this->user->id,
            'team_id' => $this->user->team_id,
        ]);

        $this->actingAs($this->user)
            ->put(route('tasks.update', $task), [
                'title' => 'Updated title',
                'priority' => 'high',
                'assignee_id' => $this->member->id,
            ]);

        $task->refresh();
        $this->assertEquals('Updated title', $task->title);
        $this->assertEquals('high', $task->priority->value);
        $this->assertEquals($this->member->id, $task->assignee_id);
    }

    public function test_move_changes_status(): void
    {
        $task = Task::factory()->create([
            'status' => TaskStatus::Todo,
            'order' => 0,
            'created_by' => $this->user->id,
            'team_id' => $this->user->team_id,
        ]);

        $this->actingAs($this->user)
            ->put(route('tasks.move', $task), [
                'status' => 'done',
                'order' => 1,
            ]);

        $task->refresh();
        $this->assertEquals('done', $task->status->value);
        $this->assertEquals(1, $task->order);
    }

    public function test_move_validates_status(): void
    {
        $task = Task::factory()->create(['created_by' => $this->user->id, 'team_id' => $this->user->team_id]);

        $this->actingAs($this->user)
            ->put(route('tasks.move', $task), [
                'status' => 'invalid',
                'order' => 0,
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_destroy_soft_deletes(): void
    {
        $task = Task::factory()->create(['created_by' => $this->user->id, 'team_id' => $this->user->team_id]);

        $this->actingAs($this->user)
            ->delete(route('tasks.destroy', $task));

        $this->assertSoftDeleted($task);
    }

    public function test_destroy_forbidden_for_member(): void
    {
        $task = Task::factory()->create(['created_by' => $this->user->id, 'team_id' => $this->user->team_id]);

        $this->actingAs($this->member)
            ->delete(route('tasks.destroy', $task))
            ->assertForbidden();
    }

    public function test_guest_redirects(): void
    {
        $this->get(route('tasks.index'))
            ->assertRedirect(route('login'));
    }
}
