<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyDigest extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(
        public readonly User $user,
    ) {
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        $this->data = [
            'name' => $user->name,
            'date' => now()->format('l, F j'),
            'meetings' => Meeting::with('user')
                ->whereBetween('start_time', [$today, $tomorrow])
                ->where('status', 'scheduled')
                ->orderBy('start_time')
                ->get()
                ->map(fn (Meeting $m) => [
                    'title' => $m->title,
                    'time' => $m->start_time->format('g:i A'),
                    'user' => $m->user?->name ?? '—',
                ]),
            'new_leads' => Lead::whereDate('created_at', $today)->count(),
            'new_clients' => Client::whereDate('created_at', $today)->count(),
            'open_tasks' => Task::whereNot('status', 'done')->count(),
            'open_tickets' => Ticket::whereNot('status', 'closed')->count(),
        ];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Daily Summary — ' . now()->format('M j, Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-digest',
        );
    }
}
