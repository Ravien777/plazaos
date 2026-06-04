<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\IntakeForm;
use App\Models\IntakeFormSubmission;
use App\Services\IntakeFormSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IntakeFormSubmissionController extends Controller
{
    public function __construct(
        private readonly IntakeFormSubmissionService $submissionService,
    ) {}

    public function show(IntakeForm $intakeForm, IntakeFormSubmission $submission): Response
    {
        $this->authorize('view', $intakeForm);

        $submission->load('data.field', 'client:id,company_name', 'form:id,title');

        return Inertia::render('IntakeForms/Submission', [
            'submission' => $submission,
        ]);
    }

    public function download(Request $request): StreamedResponse|RedirectResponse
    {
        $filePath = $request->query('path');

        if (!$filePath || !Storage::exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return Storage::download($filePath);
    }
}
