<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UploadDocumentAction;
use App\Models\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(
        private readonly UploadDocumentAction $uploadDocumentAction
    ) {}

    private function disk(): string
    {
        return config('filesystems.default') === 'r2' ? 'r2' : 'local';
    }

    public function index(string $documentableType, string $documentable): JsonResponse
    {
        $this->authorize('viewAny', Document::class);

        $model = $this->resolveDocumentable($documentableType, $documentable);

        if (!$model) {
            return response()->json(['documents' => []]);
        }

        return response()->json([
            'documents' => $model->documents()->orderBy('created_at', 'desc')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Document::class);

        $validated = $request->validate([
            'documentable_type' => ['required', 'string', 'in:client,project'],
            'documentable_id' => ['required', 'string'],
            'file' => ['required', 'file', 'max:51200', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,png,jpg,jpeg,gif,svg,zip'],
        ]);

        try {
            $document = $this->uploadDocumentAction->execute(
                $validated['documentable_type'],
                $validated['documentable_id'],
                $request->file('file'),
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to upload document.'], 500);
        }

        return response()->json(['document' => $document], 201);
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);

        if (!Storage::disk($this->disk())->exists($document->path)) {
            abort(404);
        }

        return Storage::disk($this->disk())->download($document->path, $document->name);
    }

    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        try {
            activity()->log($document, 'document.deleted', "Document {$document->name} was deleted.");

            if (Storage::disk($this->disk())->exists($document->path)) {
                Storage::disk($this->disk())->delete($document->path);
            }

            $document->delete();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete document.'], 500);
        }

        return response()->json(['message' => 'Document deleted.']);
    }

    private function resolveDocumentable(string $type, string $id): ?Model
    {
        return match ($type) {
            'client' => \App\Models\Client::find($id),
            'project' => \App\Models\Project::find($id),
            default => null,
        };
    }
}
