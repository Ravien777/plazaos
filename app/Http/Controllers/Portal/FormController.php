<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\IntakeForm;
use App\Services\IntakeFormSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class FormController extends Controller
{
    public function __construct(
        private readonly IntakeFormSubmissionService $submissionService,
    ) {}

    public function index(): Response
    {
        $client = Auth::guard('client')->user()->client;

        $forms = IntakeForm::where('is_active', true)
            ->withCount('submissions')
            ->orderBy('title')
            ->get();

        $submissions = $client->intakeFormSubmissions()
            ->with('form:id,title')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Portal/IntakeForms/Index', [
            'forms' => $forms,
            'submissions' => $submissions,
        ]);
    }

    public function show(IntakeForm $intakeForm): Response
    {
        abort_if(!$intakeForm->is_active, 404);

        $intakeForm->load('fields');

        return Inertia::render('Portal/IntakeForms/Show', [
            'form' => $intakeForm,
        ]);
    }

    public function submit(Request $request, IntakeForm $intakeForm): RedirectResponse
    {
        abort_if(!$intakeForm->is_active, 404);

        $client = Auth::guard('client')->user()->client;
        $clientUserId = Auth::guard('client')->id();

        $intakeForm->load('fields');

        $rules = [];
        foreach ($intakeForm->fields as $field) {
            if ($field->required) {
                $rules['fields.' . $field->id] = ['required'];
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $this->submissionService->submit($intakeForm, $client, $clientUserId, $request->all()['fields'] ?? []);

        return redirect()->route('portal.intake-forms.index')
            ->with('success', 'Intake form submitted successfully.');
    }
}
