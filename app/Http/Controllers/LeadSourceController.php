<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SourceType;
use App\Http\Requests\LeadSource\StoreLeadSourceRequest;
use App\Http\Requests\LeadSource\UpdateLeadSourceRequest;
use App\Models\LeadSource;
use App\Services\LeadSourceService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LeadSourceController extends Controller
{
    public function __construct(
        private readonly LeadSourceService $leadSourceService
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', LeadSource::class);

        $sources = $this->leadSourceService->list();

        return Inertia::render('LeadSources/Index', [
            'sources' => $sources,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', LeadSource::class);

        return Inertia::render('LeadSources/Create', [
            'sourceTypes' => collect(SourceType::cases())->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ]),
        ]);
    }

    public function store(StoreLeadSourceRequest $request): RedirectResponse
    {
        $this->authorize('create', LeadSource::class);

        try {
            $this->leadSourceService->create($request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create lead source. Please try again.');
        }

        return redirect()->route('lead-sources.index')->with('success', 'Lead source created successfully.');
    }

    public function edit(LeadSource $leadSource): Response
    {
        $this->authorize('update', $leadSource);

        return Inertia::render('LeadSources/Edit', [
            'source' => $leadSource,
            'sourceTypes' => collect(SourceType::cases())->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ]),
        ]);
    }

    public function update(UpdateLeadSourceRequest $request, LeadSource $leadSource): RedirectResponse
    {
        $this->authorize('update', $leadSource);

        try {
            $this->leadSourceService->update($leadSource, $request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update lead source. Please try again.');
        }

        return redirect()->route('lead-sources.index')->with('success', 'Lead source updated successfully.');
    }

    public function destroy(LeadSource $leadSource): RedirectResponse
    {
        $this->authorize('delete', $leadSource);

        try {
            $this->leadSourceService->delete($leadSource);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete lead source. Please try again.');
        }

        return redirect()->route('lead-sources.index')->with('success', 'Lead source deleted successfully.');
    }

    public function run(LeadSource $leadSource): RedirectResponse
    {
        $this->authorize('update', $leadSource);

        try {
            $this->leadSourceService->run($leadSource);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to run scraper. Please try again.');
        }

        return redirect()->route('lead-sources.index')->with('success', 'Scraping started.');
    }
}
