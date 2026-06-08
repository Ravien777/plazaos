<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Lead\StoreLeadRequest;
use App\Http\Requests\Lead\UpdateLeadRequest;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function __construct(
        private readonly LeadService $leadService
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Lead::class);

        $leads = $this->leadService->list(request()->only(['search', 'status', 'source', 'sort_field', 'sort_direction']));

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'filters' => request()->only(['search', 'status', 'source', 'sort_field', 'sort_direction']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Lead::class);

        return Inertia::render('Leads/Create');
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $this->authorize('create', Lead::class);

        try {
            $this->leadService->create($request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create lead. Please try again.');
        }

        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead): Response
    {
        $this->authorize('view', $lead);

        $lead->load(['notes', 'activities', 'emails', 'meetings']);

        return Inertia::render('Leads/Show', [
            'lead' => $lead,
        ]);
    }

    public function edit(Lead $lead): Response
    {
        $this->authorize('update', $lead);

        return Inertia::render('Leads/Edit', [
            'lead' => $lead,
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        try {
            $this->leadService->update($lead, $request->validated());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update lead. Please try again.');
        }

        return redirect()->route('leads.show', $lead)->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        try {
            $this->leadService->delete($lead);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete lead. Please try again.');
        }

        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }

    public function trash(): Response
    {
        $this->authorize('viewTrash', Lead::class);

        $leads = $this->leadService->listTrashed(request()->only(['search', 'sort_field', 'sort_direction']));

        return Inertia::render('Leads/Trash', [
            'leads' => $leads,
            'filters' => request()->only(['search', 'sort_field', 'sort_direction']),
        ]);
    }

    public function forceDestroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        $lead->forceDelete();

        return redirect()->route('leads.trash')->with('success', 'Lead permanently deleted.');
    }

    public function restore(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        $lead->restore();

        return redirect()->back()->with('success', 'Lead restored successfully.');
    }
}
