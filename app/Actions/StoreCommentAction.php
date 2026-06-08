<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Comment;
use App\Models\User;
use App\Notifications\UserMentioned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

class StoreCommentAction
{
    public function execute(Model $commentable, string $body, User $user): Comment
    {
        $comment = Comment::create([
            'commentable_type' => $commentable->getMorphClass(),
            'commentable_id' => $commentable->getKey(),
            'user_id' => $user->id,
            'body' => $body,
        ]);

        $this->notifyMentions($comment);

        return $comment;
    }

    private function notifyMentions(Comment $comment): void
    {
        preg_match_all('/@(\w+(?:\s+\w+)?)/u', $comment->body, $matches);

        $mentionedNames = array_unique($matches[1] ?? []);

        if (empty($mentionedNames)) {
            return;
        }

        $users = User::query()
            ->whereIn('name', $mentionedNames)
            ->where('id', '!=', $comment->user_id)
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new UserMentioned($comment));
    }
}
