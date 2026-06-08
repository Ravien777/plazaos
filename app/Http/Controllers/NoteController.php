<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Note;
use App\Services\NoteService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    public function __construct(
        private readonly NoteService $noteService
    ) {}

    public function index(string $noteableType, string $noteable): JsonResponse
    {
        $this->authorize('viewAny', Note::class);

        $model = $this->resolveNoteable($noteableType, $noteable);

        if (!$model) {
            abort(404);
        }

        return response()->json([
            'data' => $this->noteService->getFor($model),
        ]);
    }

    public function store(Request $request, string $noteableType, string $noteable): RedirectResponse
    {
        $this->authorize('create', Note::class);

        $validated = $request->validate(['content' => ['required', 'string']]);

        $model = $this->resolveNoteable($noteableType, $noteable);

        if (!$model) {
            abort(404);
        }

        try {
            $this->noteService->add($model, $validated['content'], Auth::user());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add note. Please try again.');
        }

        return redirect()->back()->with('success', 'Note added.');
    }

    public function update(Request $request, Note $note): RedirectResponse
    {
        $this->authorize('update', $note);

        $validated = $request->validate(['content' => ['required', 'string']]);

        try {
            $this->noteService->update($note, $validated['content']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update note. Please try again.');
        }

        return redirect()->back()->with('success', 'Note updated.');
    }

    public function destroy(Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        try {
            $this->noteService->delete($note);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete note. Please try again.');
        }

        return redirect()->back()->with('success', 'Note deleted.');
    }

    private function resolveNoteable(string $type, string $id): ?Model
    {
        $modelClass = match ($type) {
            'lead' => \App\Models\Lead::class,
            'client' => \App\Models\Client::class,
            'project' => \App\Models\Project::class,
            default => null,
        };

        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($id);
    }
}
