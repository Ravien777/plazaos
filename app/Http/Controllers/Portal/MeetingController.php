<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MeetingController extends Controller
{
    public function index(): Response
    {
        $client = Auth::guard('client')->user()->client;

        $meetings = Meeting::where('meetable_type', 'client')
            ->where('meetable_id', $client->id)
            ->orderBy('start_time', 'desc')
            ->paginate(25);

        return Inertia::render('Portal/Meetings/Index', [
            'meetings' => $meetings,
        ]);
    }
}
