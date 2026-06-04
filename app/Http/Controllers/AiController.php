<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Lead;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;

class AiController extends Controller
{
    public function __construct(
        private readonly AiService $aiService
    ) {}

    public function generateOutreach(Lead $lead): JsonResponse
    {
        $this->authorize('view', $lead);

        $result = $this->aiService->generateOutreachEmail($lead);

        if (!$result) {
            return response()->json(['message' => 'AI generation failed. Please check your API configuration.'], 500);
        }

        return response()->json(['data' => $result]);
    }

    public function generateFollowUp(Lead $lead): JsonResponse
    {
        $this->authorize('view', $lead);

        $result = $this->aiService->generateFollowUp($lead);

        if (!$result) {
            return response()->json(['message' => 'AI generation failed. Please check your API configuration.'], 500);
        }

        return response()->json(['data' => $result]);
    }

    public function summarizeWebsite(Lead $lead): JsonResponse
    {
        $this->authorize('view', $lead);

        if (!$lead->website) {
            return response()->json(['message' => 'Lead does not have a website URL.'], 422);
        }

        $summary = $this->aiService->summarizeWebsite($lead->website);

        if (!$summary) {
            return response()->json(['message' => 'Website summarization failed.'], 500);
        }

        return response()->json(['data' => ['summary' => $summary]]);
    }

    public function generateClientOutreach(Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        $result = $this->aiService->generateClientOutreachEmail($client);

        if (!$result) {
            return response()->json(['message' => 'AI generation failed. Please check your API configuration.'], 500);
        }

        return response()->json(['data' => $result]);
    }

    public function generateClientFollowUp(Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        $result = $this->aiService->generateClientFollowUp($client);

        if (!$result) {
            return response()->json(['message' => 'AI generation failed. Please check your API configuration.'], 500);
        }

        return response()->json(['data' => $result]);
    }

    public function bulkGenerateOutreach(): JsonResponse
    {
        $result = $this->aiService->generateBulkTemplate();

        if (!$result) {
            return response()->json(['message' => 'AI generation failed. Please check your API configuration.'], 500);
        }

        return response()->json(['data' => $result]);
    }
}
