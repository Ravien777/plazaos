<?php

namespace Tests\Unit\Services;

use App\Models\Lead;
use App\Models\User;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiServiceTest extends TestCase
{
    use RefreshDatabase;

    private AiService $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();
        $this->aiService = app(AiService::class);
        config(['services.openai.api_key' => 'test-key']);
        config(['services.openai.model' => 'gpt-4o']);
    }

    protected function tearDown(): void
    {
        config(['services.openai.api_key' => null]);
        config(['services.openai.model' => null]);
        parent::tearDown();
    }

    public function test_generate_outreach_returns_subject_and_body(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"subject": "Hello from AI", "body": "This is the body"}']],
                ],
            ]),
        ]);

        $lead = Lead::factory()->create();

        $result = $this->aiService->generateOutreachEmail($lead);

        $this->assertNotNull($result);
        $this->assertEquals('Hello from AI', $result['subject']);
        $this->assertEquals('This is the body', $result['body']);
    }

    public function test_generate_follow_up_returns_subject_and_body(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"subject": "Follow up", "body": "Following up"}']],
                ],
            ]),
        ]);

        $lead = Lead::factory()->create();

        $result = $this->aiService->generateFollowUp($lead);

        $this->assertNotNull($result);
        $this->assertEquals('Follow up', $result['subject']);
        $this->assertEquals('Following up', $result['body']);
    }

    public function test_generate_bulk_template_returns_subject_and_body(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"subject": "Bulk template", "body": "Bulk body with {{company_name}}"}']],
                ],
            ]),
        ]);

        $result = $this->aiService->generateBulkTemplate();

        $this->assertNotNull($result);
        $this->assertEquals('Bulk template', $result['subject']);
        $this->assertStringContainsString('{{company_name}}', $result['body']);
    }

    public function test_summarize_website_returns_summary(): void
    {
        Http::fake([
            'example.com' => Http::response('<html><body>Welcome to Example Corp. We provide excellent services.</body></html>'),
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"summary": "Example Corp is a company that provides excellent services."}']],
                ],
            ]),
        ]);

        $result = $this->aiService->summarizeWebsite('https://example.com');

        $this->assertNotNull($result);
        $this->assertStringContainsString('Example Corp', $result);
    }

    public function test_generate_outreach_returns_null_when_disabled(): void
    {
        config(['services.openai.api_key' => null]);

        $lead = Lead::factory()->create();

        $result = $this->aiService->generateOutreachEmail($lead);

        $this->assertNull($result);
    }

    public function test_handles_api_error_returns_null(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([], 500),
        ]);

        $lead = Lead::factory()->create();

        $result = $this->aiService->generateOutreachEmail($lead);

        $this->assertNull($result);
    }
}
