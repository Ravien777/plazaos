<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\IntakeFieldType;
use App\Http\Requests\IntakeForm\StoreIntakeFormRequest;
use App\Http\Requests\IntakeForm\UpdateIntakeFormRequest;
use App\Models\IntakeForm;
use App\Services\IntakeFormService;
use App\Services\IntakeFormSubmissionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class IntakeFormController extends Controller
{
    public function __construct(
        private readonly IntakeFormService $intakeFormService,
        private readonly IntakeFormSubmissionService $submissionService,
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', IntakeForm::class);

        $forms = $this->intakeFormService->list();

        return Inertia::render('IntakeForms/Index', [
            'forms' => $forms,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', IntakeForm::class);

        return Inertia::render('IntakeForms/Create', [
            'fieldTypes' => collect(IntakeFieldType::cases())->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ]),
        ]);
    }

    public function store(StoreIntakeFormRequest $request): RedirectResponse
    {
        $this->authorize('create', IntakeForm::class);

        try {
            $this->intakeFormService->create($request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create intake form. Please try again.');
        }

        return redirect()->route('intake-forms.index')->with('success', 'Intake form created successfully.');
    }

    public function show(IntakeForm $intakeForm): Response
    {
        $this->authorize('view', $intakeForm);

        $intakeForm->load('fields');

        $submissions = $this->submissionService->getSubmissionsForForm($intakeForm);

        return Inertia::render('IntakeForms/Show', [
            'form' => $intakeForm,
            'submissions' => $submissions,
        ]);
    }

    public function edit(IntakeForm $intakeForm): Response
    {
        $this->authorize('update', $intakeForm);

        $intakeForm->load('fields');

        return Inertia::render('IntakeForms/Edit', [
            'form' => $intakeForm,
            'fieldTypes' => collect(IntakeFieldType::cases())->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ]),
        ]);
    }

    public function update(UpdateIntakeFormRequest $request, IntakeForm $intakeForm): RedirectResponse
    {
        $this->authorize('update', $intakeForm);

        try {
            $this->intakeFormService->update($intakeForm, $request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update intake form. Please try again.');
        }

        return redirect()->route('intake-forms.index')->with('success', 'Intake form updated successfully.');
    }

    public function destroy(IntakeForm $intakeForm): RedirectResponse
    {
        $this->authorize('delete', $intakeForm);

        try {
            $this->intakeFormService->delete($intakeForm);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete intake form. Please try again.');
        }

        return redirect()->route('intake-forms.index')->with('success', 'Intake form deleted successfully.');
    }
}
