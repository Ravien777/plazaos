<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectBulkController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService
    ) {}

    public function destroy(Request $request): RedirectResponse
    {
        $this->authorize('delete', new Project);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:projects,id'],
        ]);

        try {
            $this->projectService->bulkArchive($data['ids']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to archive projects. Please try again.');
        }

        return redirect()->back()->with('success', count($data['ids']) . ' project(s) archived successfully.');
    }

    public function forceDestroy(Request $request): RedirectResponse
    {
        $this->authorize('delete', new Project);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:projects,id'],
        ]);

        try {
            $this->projectService->bulkForceDelete($data['ids']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete projects. Please try again.');
        }

        return redirect()->back()->with('success', count($data['ids']) . ' project(s) permanently deleted.');
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $this->authorize('update', new Project);

        $statuses = array_map(fn (ProjectStatus $case) => $case->value, ProjectStatus::cases());

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:projects,id'],
            'status' => ['required', 'string', 'in:' . implode(',', $statuses)],
        ]);

        try {
            $this->projectService->bulkUpdateStatus($data['ids'], $data['status']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update projects. Please try again.');
        }

        return redirect()->back()->with('success', count($data['ids']) . ' project(s) updated successfully.');
    }
}
