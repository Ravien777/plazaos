<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
        config(['services.openai.api_key' => 'test-key']);
    }

    protected function tearDown(): void
    {
        config(['services.openai.api_key' => null]);
        parent::tearDown();
    }

    public function test_generate_outreach_returns_json(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"subject": "AI Subject", "body": "AI Body"}']],
                ],
            ]),
        ]);

        $lead = Lead::factory()->create();

        $response = $this->postJson(route('ai.generate-outreach', $lead));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['subject', 'body']]);
    }

    public function test_generate_follow_up_returns_json(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"subject": "Follow Up", "body": "Follow up body"}']],
                ],
            ]),
        ]);

        $lead = Lead::factory()->create();

        $response = $this->postJson(route('ai.generate-follow-up', $lead));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['subject', 'body']]);
    }

    public function test_summarize_website_returns_summary(): void
    {
        Http::fake([
            'example.com' => Http::response('<html>Welcome</html>'),
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"summary": "Great company summary"}']],
                ],
            ]),
        ]);

        $lead = Lead::factory()->create(['website' => 'https://example.com']);

        $response = $this->postJson(route('ai.summarize-website', $lead));

        $response->assertOk()
            ->assertJson(['data' => ['summary' => 'Great company summary']]);
    }

    public function test_summarize_website_requires_website(): void
    {
        $lead = Lead::factory()->create(['website' => null]);

        $response = $this->postJson(route('ai.summarize-website', $lead));

        $response->assertStatus(422);
    }

    public function test_bulk_generate_outreach_returns_json(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"subject": "Bulk", "body": "Bulk body"}']],
                ],
            ]),
        ]);

        $response = $this->postJson(route('ai.bulk-generate-outreach'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['subject', 'body']]);
    }

    public function test_guest_redirects_to_login(): void
    {
        auth()->logout();

        $lead = Lead::factory()->create();

        $response = $this->postJson(route('ai.generate-outreach', $lead));

        $response->assertUnauthorized();
    }
}
