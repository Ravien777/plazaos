<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientExportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['id' => 1]);
        $this->actingAs($user);
    }

    public function test_export_returns_csv(): void
    {
        Client::factory()->count(3)->create();

        $response = $this->get(route('clients.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type') ?? '');
        $this->assertStringContainsString('clients-export-', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_export_filters_by_status(): void
    {
        Client::factory()->create(['company_name' => 'Active Client', 'status' => 'active']);
        Client::factory()->create(['company_name' => 'Inactive Client', 'status' => 'inactive']);

        $response = $this->get(route('clients.export', ['status' => 'active']));

        $response->assertOk();
    }

    public function test_export_respects_ids_param(): void
    {
        $clients = Client::factory()->count(3)->create();

        $response = $this->get(route('clients.export', [
            'ids' => [$clients[0]->id, $clients[1]->id],
        ]));

        $response->assertOk();
    }
}
