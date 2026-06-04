<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailController extends Controller
{
    public function __construct(
        private readonly EmailService $emailService
    ) {}

    public function templates(): JsonResponse
    {
        $this->authorize('viewAny', Email::class);

        $configTemplates = collect(config('email-templates', []))->map(fn ($t, $key) => [
            'key' => $key,
            'name' => $t['name'],
            'subject' => $t['subject'],
            'body' => $t['body'],
            'variables' => $t['variables'],
        ])->values();

        $dbTemplates = EmailTemplate::orderBy('name')->get()->map(fn ($t) => [
            'key' => $t->key,
            'name' => $t->name,
            'subject' => $t->subject,
            'body' => $t->body,
            'variables' => $t->variables ?? [],
        ]);

        return response()->json(['data' => $configTemplates->concat($dbTemplates)]);
    }

    public function index(string $emailableType, string $id): JsonResponse
    {
        $this->authorize('viewAny', Email::class);

        $model = $this->resolveEmailable($emailableType, $id);

        if (!$model) {
            abort(404);
        }

        return response()->json([
            'data' => $this->emailService->getFor($model),
        ]);
    }

    public function store(Request $request, string $emailableType, string $id): RedirectResponse
    {
        $this->authorize('create', Email::class);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:998'],
            'body' => ['required', 'string'],
            'template' => ['nullable', 'string'],
        ]);

        $model = $this->resolveEmailable($emailableType, $id);

        if (!$model) {
            abort(404);
        }

        try {
            $this->emailService->sendCustom(
                $model,
                $validated['subject'],
                $validated['body'],
                $validated['template'] ?? 'custom',
                Auth::user()
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send email. Please try again.');
        }

        return redirect()->back()->with('success', 'Email sent.');
    }

    private function resolveEmailable(string $type, string $id): ?Model
    {
        $modelClass = match ($type) {
            'lead' => \App\Models\Lead::class,
            'client' => \App\Models\Client::class,
            'project' => \App\Models\Project::class,
            default => null,
        };

        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($id);
    }
}
