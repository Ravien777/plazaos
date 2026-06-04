<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ImportLeadsJob;
use App\Models\Lead;
use App\Models\LeadImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use League\Csv\Reader;

class LeadImportController extends Controller
{
    public function create(): Response
    {
        $this->authorize('create', Lead::class);

        return Inertia::render('Leads/Import');
    }

    public function preview(Request $request): JsonResponse
    {
        $this->authorize('create', Lead::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:256000'],
        ]);

        $path = $request->file('file')->store('imports');
        $csv = Reader::createFromPath(Storage::path($path));
        $csv->setHeaderOffset(0);

        $headers = $csv->getHeader();
        $sampleRows = [];
        foreach ($csv->getRecords() as $i => $row) {
            if ($i >= 3) break;
            $sampleRows[] = $row;
        }

        Storage::delete($path);

        return response()->json([
            'headers' => $headers,
            'sample_rows' => $sampleRows,
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('create', Lead::class);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:256000'],
            'column_mapping' => ['required', 'json'],
            'duplicate_strategy' => ['required', 'in:skip,update'],
        ]);

        $mapping = json_decode($validated['column_mapping'], true);
        $path = $request->file('file')->store('imports');

        $csv = Reader::createFromPath(Storage::path($path));
        $csv->setHeaderOffset(0);
        $records = iterator_to_array($csv->getRecords());
        $totalRows = count($records);

        try {
            $import = LeadImport::create([
                'filename' => $request->file('file')->getClientOriginalName(),
                'filepath' => $path,
                'column_mapping' => ['fields' => $mapping],
                'duplicate_strategy' => $validated['duplicate_strategy'],
                'total_rows' => $totalRows,
                'status' => 'pending',
                'user_id' => Auth::id(),
            ]);

            ImportLeadsJob::dispatch($import);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to start import. Please try again.');
        }

        return redirect()->route('leads.import.progress', $import)
            ->with('success', 'Import started.');
    }

    public function progress(LeadImport $import): JsonResponse
    {
        $this->authorize('view', $import);

        return response()->json([
            'id' => $import->id,
            'status' => $import->status,
            'total_rows' => $import->total_rows,
            'processed' => $import->processed,
            'failed' => $import->failed,
            'errors' => $import->errors,
        ]);
    }

    public function show(LeadImport $import): Response
    {
        $this->authorize('view', $import);

        return Inertia::render('Leads/Import', [
            'import' => $import,
        ]);
    }
}
