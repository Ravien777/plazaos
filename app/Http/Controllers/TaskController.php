<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Task::with('assignee')
            ->orderBy('status')
            ->orderBy('order')
            ->get()
            ->groupBy('status');

        $projects = Project::select('id', 'name')->get();
        $assignees = User::where('team_id', auth()->user()->team_id)
            ->select('id', 'name')
            ->get();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'projects' => $projects,
            'assignees' => $assignees,
            'filters' => request()->only(['project_id', 'assignee_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'status' => 'required|string|in:todo,in_progress,done',
        ]);

        $maxOrder = Task::where('status', $validated['status'])->max('order') ?? 0;

        Task::create([
            'title' => $validated['title'],
            'project_id' => $validated['project_id'] ?? null,
            'status' => $validated['status'],
            'order' => $maxOrder + 1,
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Task created.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|string|in:low,medium,high',
            'assignee_id' => 'nullable|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $task->update($validated);

        return redirect()->back()->with('success', 'Task updated.');
    }

    public function move(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'status' => 'required|string|in:todo,in_progress,done',
            'order' => 'required|integer|min:0',
        ]);

        $task->update(['status' => $validated['status'], 'order' => $validated['order']]);

        return redirect()->back()->with('success', 'Task moved.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->back()->with('success', 'Task deleted.');
    }
}
