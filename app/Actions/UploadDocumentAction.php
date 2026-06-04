<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Client;
use App\Models\Document;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use App\Notifications\DocumentUploaded;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadDocumentAction
{
    public function execute(string $documentableType, string $documentableId, UploadedFile $file): Document
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $disk = config('filesystems.default') === 'r2' ? 'r2' : 'local';
        $path = $file->storeAs('documents', $filename, $disk);

        $document = Document::create([
            'documentable_type' => $documentableType,
            'documentable_id' => $documentableId,
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'user_id' => Auth::id(),
        ]);
        activity()->log($document, 'document.uploaded', "Document {$document->name} was uploaded.");

        $parent = match ($documentableType) {
            'lead' => Lead::find($documentableId),
            'client' => Client::find($documentableId),
            'project' => Project::find($documentableId),
            default => null,
        };
        Notification::send(User::first(), new DocumentUploaded($file->getClientOriginalName(), $parent));

        return $document;
    }
}
