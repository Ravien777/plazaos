<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ConvertLeadToClientAction;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;

class LeadConversionController extends Controller
{
    public function __construct(
        private readonly ConvertLeadToClientAction $convertAction
    ) {}

    public function convert(Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        if ($lead->converted_at) {
            return redirect()->back()->with('error', 'This lead has already been converted.');
        }

        try {
            $client = $this->convertAction->execute($lead);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to convert lead. Please try again.');
        }

        return redirect()->route('clients.show', $client)
            ->with('success', 'Lead converted to client successfully.');
    }
}
