<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardLayoutController extends Controller
{
    private const array KNOWN_STAT_CARDS = [
        'stat-new-leads',
        'stat-active-leads',
        'stat-active-clients',
        'stat-open-projects',
        'stat-open-tickets',
        'stat-upcoming-meetings',
    ];

    private const array KNOWN_BOTTOM_WIDGETS = [
        'meetings',
        'activity',
        'wall-of-love',
    ];

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'stat_cards' => 'required|array',
            'stat_cards.*' => 'string|in:' . implode(',', self::KNOWN_STAT_CARDS),
            'bottom_widgets' => 'required|array',
            'bottom_widgets.*' => 'string|in:' . implode(',', self::KNOWN_BOTTOM_WIDGETS),
        ]);

        $request->user()->update([
            'dashboard_layout' => $validated,
        ]);

        return redirect()->back();
    }
}
