<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    protected array $validSteps = ['profile', 'team', 'first_lead', 'first_project', 'integrations'];

    public function updateStep(Request $request, string $step): RedirectResponse
    {
        if (!in_array($step, $this->validSteps, true)) {
            return redirect()->back();
        }

        $request->user()->completeOnboardingStep($step);

        return redirect()->back()->with('success', 'Progress saved.');
    }

    public function skip(Request $request): RedirectResponse
    {
        $data = $request->user()->onboarding_data ?? $request->user()->defaultOnboardingData();
        $data['skipped'] = true;
        $data['completed'] = true;
        $request->user()->onboarding_data = $data;
        $request->user()->save();

        return redirect()->back()->with('info', 'Onboarding skipped. You can always come back.');
    }

    public function complete(Request $request): RedirectResponse
    {
        $data = $request->user()->onboarding_data ?? $request->user()->defaultOnboardingData();
        $data['completed'] = true;
        $request->user()->onboarding_data = $data;
        $request->user()->save();

        return redirect()->back()->with('success', 'Onboarding completed!');
    }

    public function dismiss(Request $request): RedirectResponse
    {
        $data = $request->user()->onboarding_data ?? $request->user()->defaultOnboardingData();
        $data['dismissed'] = true;
        $request->user()->onboarding_data = $data;
        $request->user()->save();

        return redirect()->back();
    }

    public function wizardSeen(Request $request): RedirectResponse
    {
        $data = $request->user()->onboarding_data ?? $request->user()->defaultOnboardingData();
        $data['wizard_seen'] = true;
        $request->user()->onboarding_data = $data;
        $request->user()->save();

        return redirect()->back();
    }
}
