<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\EmailService;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadBulkController extends Controller
{
    public function __construct(
        private readonly LeadService $leadService,
        private readonly EmailService $emailService,
    ) {}

    public function destroy(Request $request): RedirectResponse
    {
        $this->authorize('delete', new Lead);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:leads,id'],
        ]);

        try {
            $this->leadService->bulkDelete($data['ids']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete leads. Please try again.');
        }

        return redirect()->back()->with('success', count($data['ids']) . ' lead(s) deleted successfully.');
    }

    public function destroyByFilters(Request $request): JsonResponse
    {
        $this->authorize('delete', new Lead);

        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:new,qualified,contacted,interested,meeting_scheduled,proposal_sent,won,lost'],
            'source' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $count = $this->leadService->bulkDeleteByFilters($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete leads.'], 500);
        }

        return response()->json(['deleted' => $count]);
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $this->authorize('update', new Lead);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:leads,id'],
            'status' => ['required', 'string', 'in:new,qualified,contacted,interested,meeting_scheduled,proposal_sent,won,lost'],
        ]);

        try {
            $this->leadService->bulkUpdateStatus($data['ids'], $data['status']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update leads. Please try again.');
        }

        return redirect()->back()->with('success', count($data['ids']) . ' lead(s) updated successfully.');
    }

    public function sendEmail(Request $request): RedirectResponse
    {
        $this->authorize('create', Lead::class);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:leads,id'],
            'subject' => ['required', 'string', 'max:998'],
            'body' => ['required', 'string'],
        ]);

        $leads = Lead::whereIn('id', $data['ids'])->get();
        $sent = 0;
        $failed = 0;

        foreach ($leads as $lead) {
            if (!$lead->email) {
                $failed++;
                continue;
            }

            try {
                $this->emailService->sendCustom(
                    $lead,
                    $data['subject'],
                    $data['body'],
                    'custom',
                    Auth::user()
                );
                $sent++;
            } catch (\Exception) {
                $failed++;
            }
        }

        $message = "Email sent to {$sent} lead(s).";
        if ($failed > 0) {
            $message .= " {$failed} lead(s) skipped (no email or send failed).";
        }

        return redirect()->back()->with('success', $message);
    }
}
