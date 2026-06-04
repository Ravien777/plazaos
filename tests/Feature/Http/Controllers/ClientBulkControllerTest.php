<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientBulkControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['id' => 1]);
        $this->actingAs($user);
    }

    public function test_bulk_delete_removes_selected_clients(): void
    {
        $clients = Client::factory()->count(3)->create();

        $response = $this->post(route('clients.bulk.delete'), [
            'ids' => $clients->pluck('id')->toArray(),
        ]);

        $response->assertSessionHas('success');
        $this->assertSoftDeleted($clients[0]);
        $this->assertSoftDeleted($clients[1]);
        $this->assertSoftDeleted($clients[2]);
    }

    public function test_bulk_delete_validates_empty_ids(): void
    {
        $response = $this->post(route('clients.bulk.delete'), [
            'ids' => [],
        ]);

        $response->assertSessionHasErrors(['ids']);
    }

    public function test_bulk_update_status_changes_selected_clients(): void
    {
        $clients = Client::factory()->count(3)->create(['status' => 'active']);

        $response = $this->post(route('clients.bulk.status'), [
            'ids' => $clients->pluck('id')->toArray(),
            'status' => 'inactive',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('inactive', $clients[0]->fresh()->status);
        $this->assertEquals('inactive', $clients[1]->fresh()->status);
        $this->assertEquals('inactive', $clients[2]->fresh()->status);
    }

    public function test_bulk_update_status_validates_invalid_status(): void
    {
        $clients = Client::factory()->count(2)->create();

        $response = $this->post(route('clients.bulk.status'), [
            'ids' => $clients->pluck('id')->toArray(),
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['status']);
    }
}
