<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RequestTestimonialAction;
use App\Models\Client;
use App\Models\Project;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends Controller
{
    public function __construct(
        private readonly RequestTestimonialAction $requestTestimonialAction
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Testimonial::class);

        $testimonials = Testimonial::with('client:id,company_name')
            ->latest()
            ->paginate(25);

        return Inertia::render('Testimonials/Index', [
            'testimonials' => $testimonials,
        ]);
    }

    public function show(string $token): Response
    {
        $testimonial = Testimonial::where('review_token', $token)->firstOrFail();

        if ($testimonial->submitted_at) {
            return Inertia::render('Reviews/Show', [
                'submitted' => true,
                'testimonial' => null,
            ]);
        }

        return Inertia::render('Reviews/Show', [
            'submitted' => false,
            'testimonial' => $testimonial->only('id', 'review_token'),
        ]);
    }

    public function submit(Request $request, string $token): RedirectResponse
    {
        $testimonial = Testimonial::where('review_token', $token)->firstOrFail();

        abort_if((bool) $testimonial->submitted_at, 410);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['nullable', 'string', 'max:2000'],
        ]);

        $testimonial->update([
            'rating' => $data['rating'],
            'content' => $data['content'],
            'is_approved' => true,
            'submitted_at' => now(),
        ]);

        return redirect()->route('review.thanks', ['token' => $token]);
    }

    public function thanks(string $token): Response
    {
        $testimonial = Testimonial::where('review_token', $token)->firstOrFail();

        return Inertia::render('Reviews/Show', [
            'submitted' => true,
            'testimonial' => null,
        ]);
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->authorize('delete', $testimonial);

        $testimonial->delete();

        return redirect()->back()->with('success', 'Testimonial removed.');
    }

    public function requestFromClient(Client $client, Request $request): JsonResponse
    {
        $this->authorize('update', $client);

        $testimonial = $this->requestTestimonialAction->execute($client, null, $request->user());

        return response()->json([
            'message' => 'Review requested! Email sent to ' . $client->email . '.',
            'testimonial_id' => $testimonial->id,
        ]);
    }

    public function requestFromProject(Project $project, Request $request): JsonResponse
    {
        $this->authorize('update', $project);

        $this->requestTestimonialAction->execute($project->client, $project, $request->user());

        return response()->json([
            'message' => 'Review requested! Email sent to ' . $project->client->email . '.',
        ]);
    }
}
