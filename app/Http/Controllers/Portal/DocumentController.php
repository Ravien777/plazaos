<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(): Response
    {
        $client = Auth::guard('client')->user()->client;

        $documents = Document::where('documentable_type', 'client')
            ->where('documentable_id', $client->id)
            ->latest()
            ->get();

        return Inertia::render('Portal/Documents/Index', [
            'documents' => $documents,
        ]);
    }

    public function download(Document $document): StreamedResponse
    {
        $client = Auth::guard('client')->user()->client;

        abort_if($document->documentable_type !== 'client' || $document->documentable_id !== $client->id, 403);

        return Storage::disk($document->disk ?? 'public')->download($document->path, $document->name);
    }
}
