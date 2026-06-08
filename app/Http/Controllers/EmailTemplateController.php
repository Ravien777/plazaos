<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailTemplateController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', EmailTemplate::class);

        $templates = EmailTemplate::orderBy('name')->get();

        return Inertia::render('Templates/Index', [
            'templates' => $templates,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', EmailTemplate::class);

        return Inertia::render('Templates/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', EmailTemplate::class);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', 'unique:email_templates,key'],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:998'],
            'body' => ['required', 'string'],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['string', 'max:100'],
        ]);

        EmailTemplate::create($validated);

        return redirect()->route('templates.index')->with('success', 'Email template created.');
    }

    public function edit(EmailTemplate $template): Response
    {
        $this->authorize('update', $template);

        return Inertia::render('Templates/Edit', [
            'template' => $template,
        ]);
    }

    public function update(Request $request, EmailTemplate $template): RedirectResponse
    {
        $this->authorize('update', $template);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', 'unique:email_templates,key,' . $template->id],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:998'],
            'body' => ['required', 'string'],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['string', 'max:100'],
        ]);

        $template->update($validated);

        return redirect()->route('templates.index')->with('success', 'Email template updated.');
    }

    public function destroy(EmailTemplate $template): RedirectResponse
    {
        $this->authorize('delete', $template);

        $template->delete();

        return redirect()->route('templates.index')->with('success', 'Email template deleted.');
    }
}
