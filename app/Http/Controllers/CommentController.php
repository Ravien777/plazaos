<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\StoreCommentAction;
use App\Models\Client;
use App\Models\Comment;
use App\Models\Lead;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        private readonly StoreCommentAction $storeCommentAction,
    ) {}

    public function index(string $commentableType, string $commentable): JsonResponse
    {
        $model = $this->resolveCommentable($commentableType, $commentable);

        if (!$model) {
            abort(404);
        }

        $comments = $model->comments()
            ->with('user')
            ->latest()
            ->get()
            ->map(fn (Comment $comment) => [
                'id' => $comment->id,
                'body' => $comment->body,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'avatar' => $comment->user->avatar,
                ],
                'created_at' => $comment->created_at,
                'can_delete' => request()->user()?->id === $comment->user_id,
            ]);

        return response()->json(['data' => $comments]);
    }

    public function store(Request $request, string $commentableType, string $commentable): RedirectResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $model = $this->resolveCommentable($commentableType, $commentable);

        if (!$model) {
            abort(404);
        }

        $this->storeCommentAction->execute($model, $validated['body'], $request->user());

        return redirect()->back()->with('success', 'Comment added.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return redirect()->back()->with('success', 'Comment removed.');
    }

    private function resolveCommentable(string $type, string $id): ?Model
    {
        $modelClass = match ($type) {
            'lead' => Lead::class,
            'client' => Client::class,
            'project' => Project::class,
            default => null,
        };

        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($id);
    }
}
