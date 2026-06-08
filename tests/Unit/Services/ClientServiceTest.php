<?php

namespace Tests\Unit\Services;

use App\Models\Client;
use App\Models\User;
use App\Services\ClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClientService $clientService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clientService = app(ClientService::class);
        User::factory()->create();
    }

    public function test_list_returns_paginated_results(): void
    {
        Client::factory()->count(3)->create();

        $result = $this->clientService->list();

        $this->assertCount(3, $result->items());
    }

    public function test_list_filters_by_search(): void
    {
        Client::factory()->create(['company_name' => 'Acme Corp']);
        Client::factory()->create(['company_name' => 'Other Inc']);

        $result = $this->clientService->list(['search' => 'Acme']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_status(): void
    {
        Client::factory()->create(['status' => 'active']);
        Client::factory()->create(['status' => 'inactive']);

        $result = $this->clientService->list(['status' => 'active']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_respects_per_page(): void
    {
        Client::factory()->count(5)->create();

        $result = $this->clientService->list(['per_page' => 2]);

        $this->assertCount(2, $result->items());
        $this->assertEquals(5, $result->total());
    }

    public function test_create_creates_client_and_logs_activity(): void
    {
        $data = Client::factory()->make()->toArray();
        $client = $this->clientService->create($data);

        $this->assertDatabaseHas('clients', ['id' => $client->id]);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'event' => 'client.created',
        ]);
    }

    public function test_update_updates_client_and_logs_activity(): void
    {
        $client = Client::factory()->create();

        $updated = $this->clientService->update($client, ['company_name' => 'Updated Corp']);

        $this->assertEquals('Updated Corp', $updated->company_name);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'event' => 'client.updated',
        ]);
    }

    public function test_delete_deletes_client_and_logs_activity(): void
    {
        $client = Client::factory()->create();

        $this->clientService->delete($client);

        $this->assertSoftDeleted($client);
        $this->assertDatabaseHas('activities', [
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'event' => 'client.deleted',
        ]);
    }
}
