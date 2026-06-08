<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    private const OPENAI_URL = 'https://api.openai.com/v1/chat/completions';

    public function configured(): bool
    {
        return !empty($this->setting('api_key'));
    }

    public function generateOutreachEmail(Lead $lead): ?array
    {
        if (!$this->configured()) {
            return null;
        }

        $prompt = $this->buildOutreachPrompt($lead);
        $response = $this->callOpenAI($prompt);

        if (!$response) {
            return null;
        }

        return [
            'subject' => $response['subject'] ?? '',
            'body' => $response['body'] ?? '',
        ];
    }

    public function generateFollowUp(Lead $lead): ?array
    {
        if (!$this->configured()) {
            return null;
        }

        $prompt = $this->buildFollowUpPrompt($lead);
        $response = $this->callOpenAI($prompt);

        if (!$response) {
            return null;
        }

        return [
            'subject' => $response['subject'] ?? '',
            'body' => $response['body'] ?? '',
        ];
    }

    public function generateBulkTemplate(): ?array
    {
        if (!$this->configured()) {
            return null;
        }

        $prompt = 'Write a professional cold outreach email template for a marketing agency. '
            . 'Use {{company_name}} and {{contact_name}} as placeholders so it can be personalized for any lead. '
            . 'Keep the tone professional and value-focused. '
            . 'Return a JSON object with "subject" and "body" keys.';

        $response = $this->callOpenAI($prompt);

        if (!$response) {
            return null;
        }

        return [
            'subject' => $response['subject'] ?? '',
            'body' => $response['body'] ?? '',
        ];
    }

    public function generateClientOutreachEmail(Client $client): ?array
    {
        if (!$this->configured()) {
            return null;
        }

        $prompt = $this->buildClientOutreachPrompt($client);
        $response = $this->callOpenAI($prompt);

        if (!$response) {
            return null;
        }

        return [
            'subject' => $response['subject'] ?? '',
            'body' => $response['body'] ?? '',
        ];
    }

    public function generateClientFollowUp(Client $client): ?array
    {
        if (!$this->configured()) {
            return null;
        }

        $prompt = $this->buildClientFollowUpPrompt($client);
        $response = $this->callOpenAI($prompt);

        if (!$response) {
            return null;
        }

        return [
            'subject' => $response['subject'] ?? '',
            'body' => $response['body'] ?? '',
        ];
    }

    public function summarizeWebsite(string $url): ?string
    {
        if (!$this->configured()) {
            return null;
        }

        try {
            $pageContent = Http::timeout(10)->get($url)->body();
        } catch (\Exception $e) {
            Log::warning('AiService: Failed to fetch website content', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }

        $text = strip_tags($pageContent);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = mb_substr($text, 0, 4000);

        $prompt = "Summarize the following company website content in 3-4 clear sentences:\n\n{$text}";

        $response = $this->callOpenAI($prompt);

        if (!$response) {
            return null;
        }

        return $response['summary'] ?? $response['body'] ?? '';
    }

    private function buildOutreachPrompt(Lead $lead): string
    {
        return "Write a personalized outreach email for the following lead. "
            . "Return a JSON object with \"subject\" and \"body\" keys (no markdown, no code fences).\n\n"
            . "Company: {$lead->company_name}\n"
            . "Contact: {$lead->contact_name}\n"
            . "Industry: {$lead->industry}\n"
            . "Email: {$lead->email}\n"
            . "Notes: {$lead->notes}\n\n"
            . "The email should be professional, concise, and value-focused. Mention the company name and contact name naturally.";
    }

    private function buildClientOutreachPrompt(Client $client): string
    {
        return "Write a personalized outreach email for the following client. "
            . "Return a JSON object with \"subject\" and \"body\" keys (no markdown, no code fences).\n\n"
            . "Company: {$client->company_name}\n"
            . "Contact: {$client->contact_name}\n"
            . "Industry: {$client->industry}\n"
            . "Email: {$client->email}\n"
            . "Notes: {$client->notes}\n\n"
            . "The email should be professional, concise, and value-focused. Mention the company name and contact name naturally.";
    }

    private function buildClientFollowUpPrompt(Client $client): string
    {
        return "Write a brief follow-up email for the following client. "
            . "Return a JSON object with \"subject\" and \"body\" keys (no markdown, no code fences).\n\n"
            . "Company: {$client->company_name}\n"
            . "Contact: {$client->contact_name}\n"
            . "Industry: {$client->industry}\n\n"
            . "The email should be polite, persistent but not pushy. Reference a previous outreach.";
    }

    private function buildFollowUpPrompt(Lead $lead): string
    {
        return "Write a brief follow-up email for the following lead. "
            . "Return a JSON object with \"subject\" and \"body\" keys (no markdown, no code fences).\n\n"
            . "Company: {$lead->company_name}\n"
            . "Contact: {$lead->contact_name}\n"
            . "Industry: {$lead->industry}\n\n"
            . "The email should be polite, persistent but not pushy. Reference a previous outreach.";
    }

    private function setting(string $key): mixed
    {
        $user = User::first();
        $dbValue = $user?->getSetting("openai_{$key}");

        return $dbValue ?? config("services.openai.{$key}");
    }

    private function callOpenAI(string $prompt): ?array
    {
        $apiKey = $this->setting('api_key');
        $model = $this->setting('model');

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post(self::OPENAI_URL, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an AI assistant that helps write professional business emails. Always respond with valid JSON.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 500,
                ]);

            if (!$response->successful()) {
                Log::warning('AiService: OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                return null;
            }

            $parsed = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['body' => $content];
            }

            return $parsed;
        } catch (\Exception $e) {
            Log::error('AiService: Exception calling OpenAI', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
