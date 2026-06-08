<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Services\MaroniService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class MaroniControllerTest extends TestCase
{
    use RefreshDatabase;

    private $maroniService;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->maroniService = Mockery::mock(MaroniService::class);
        $this->maroniService->shouldReceive('isConfigured')->andReturn(false)->byDefault();
        $this->app->instance(MaroniService::class, $this->maroniService);
    }

    public function test_summary_returns_json(): void
    {
        $client = Client::factory()->create(['maroni_client_id' => 'maroni-123']);

        $this->maroniService->shouldReceive('isConfigured')->andReturnTrue();
        $this->maroniService->shouldReceive('getClientSummary')
            ->once()
            ->with(Mockery::on(fn ($c) => $c->id === $client->id))
            ->andReturn(['total_invoiced' => 1000]);

        $response = $this->getJson(route('maroni.clients.summary', $client));

        $response->assertOk();
        $response->assertJson([
            'configured' => true,
            'summary' => ['total_invoiced' => 1000],
        ]);
    }

    public function test_invoices_returns_json(): void
    {
        $client = Client::factory()->create();

        $this->maroniService->shouldReceive('isConfigured')->andReturnTrue();
        $this->maroniService->shouldReceive('getClientInvoices')
            ->once()
            ->with(Mockery::on(fn ($c) => $c->id === $client->id))
            ->andReturn([['id' => 'inv-1', 'total' => 500]]);

        $response = $this->getJson(route('maroni.clients.invoices', $client));

        $response->assertOk();
        $response->assertJson([
            'configured' => true,
            'invoices' => [['id' => 'inv-1', 'total' => 500]],
        ]);
    }

    public function test_expenses_returns_json(): void
    {
        $client = Client::factory()->create();

        $this->maroniService->shouldReceive('isConfigured')->andReturnTrue();
        $this->maroniService->shouldReceive('getClientExpenses')
            ->once()
            ->with(Mockery::on(fn ($c) => $c->id === $client->id))
            ->andReturn([['id' => 'exp-1', 'amount' => 250]]);

        $response = $this->getJson(route('maroni.clients.expenses', $client));

        $response->assertOk();
        $response->assertJsonStructure(['configured', 'expenses']);
    }

    public function test_sync_all_syncs_clients(): void
    {
        Client::factory()->count(2)->create(['maroni_client_id' => null]);

        $this->maroniService->shouldReceive('isConfigured')->andReturnTrue();
        $this->maroniService->shouldReceive('syncClient')
            ->twice()
            ->andReturn('maroni-client-id');

        $response = $this->postJson(route('maroni.sync'));

        $response->assertOk();
        $response->assertJson(['synced' => 2]);
    }

    public function test_sync_all_returns_error_when_not_configured(): void
    {
        $this->maroniService->shouldReceive('isConfigured')->andReturnFalse();

        $response = $this->postJson(route('maroni.sync'));

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Maroni not configured.']);
    }

    public function test_guest_redirects(): void
    {
        $this->post('/logout');

        $this->post(route('maroni.sync'))
            ->assertRedirect(route('login'));
    }
}
