<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class NoteService
{
    public function getFor(Model $noteable): Collection
    {
        return $noteable->notes()->with('noteable')->latest()->get();
    }

    public function add(Model $noteable, string $content, User $user): Note
    {
        $note = $noteable->notes()->create([
            'content' => $content,
            'created_by' => $user->id,
        ]);

        activity()->log($noteable, 'note.added', 'A note was added.');

        return $note;
    }

    public function update(Note $note, string $content): Note
    {
        $note->update(['content' => $content]);

        activity()->log($note->noteable, 'note.updated', 'A note was updated.');

        return $note;
    }

    public function delete(Note $note): void
    {
        $noteable = $note->noteable;

        $note->delete();

        activity()->log($noteable, 'note.deleted', 'A note was deleted.');
    }
}
