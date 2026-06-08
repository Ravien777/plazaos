<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Resend\Contracts\Client as ResendClient;
use Tests\TestCase;

class EmailControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);

        $emailsMock = Mockery::mock();
        $emailsMock->shouldReceive('send')->andReturn((object) ['id' => 'resend-test-id']);

        $clientMock = Mockery::mock(ResendClient::class);
        $clientMock->shouldReceive('emails')->andReturn($emailsMock);

        $this->app->instance(ResendClient::class, $clientMock);
    }

    public function test_templates_returns_templates(): void
    {
        $response = $this->get(route('email.templates'));

        $response->assertOk();
        $response->assertJsonStructure(['data' => [['key', 'name', 'subject']]]);
    }

    public function test_store_sends_email_and_redirects(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->post(route('emails.store', ['lead', $lead->id]), [
            'subject' => 'Hello',
            'body' => 'Just checking in.',
        ]);

        $response->assertRedirect();
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $lead = Lead::factory()->create();
        $this->get(route('emails.index', ['lead', $lead->id]))->assertRedirect(route('login'));
    }
}
