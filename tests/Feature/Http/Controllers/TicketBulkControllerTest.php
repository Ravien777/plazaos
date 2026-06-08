<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketBulkControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_bulk_delete_archives_tickets(): void
    {
        $tickets = Ticket::factory()->count(3)->create();

        $response = $this->post(route('tickets.bulk.delete'), [
            'ids' => $tickets->pluck('id')->toArray(),
        ]);

        $response->assertSessionHas('success');
        $this->assertSoftDeleted($tickets[0]);
        $this->assertSoftDeleted($tickets[1]);
        $this->assertSoftDeleted($tickets[2]);
    }

    public function test_bulk_delete_validates_empty_ids(): void
    {
        $response = $this->post(route('tickets.bulk.delete'), [
            'ids' => [],
        ]);

        $response->assertSessionHasErrors(['ids']);
    }

    public function test_bulk_force_delete_removes(): void
    {
        $tickets = Ticket::factory()->count(3)->create();

        $response = $this->post(route('tickets.bulk.force-delete'), [
            'ids' => $tickets->pluck('id')->toArray(),
        ]);

        $response->assertSessionHas('success');
        $this->assertModelMissing($tickets[0]);
        $this->assertModelMissing($tickets[1]);
        $this->assertModelMissing($tickets[2]);
    }

    public function test_bulk_update_status_changes(): void
    {
        $tickets = Ticket::factory()->count(3)->create(['status' => 'open']);

        $response = $this->post(route('tickets.bulk.status'), [
            'ids' => $tickets->pluck('id')->toArray(),
            'status' => 'closed',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('closed', $tickets[0]->fresh()->status->value);
        $this->assertEquals('closed', $tickets[1]->fresh()->status->value);
        $this->assertEquals('closed', $tickets[2]->fresh()->status->value);
    }

    public function test_bulk_update_status_validates_invalid(): void
    {
        $tickets = Ticket::factory()->count(2)->create();

        $response = $this->post(route('tickets.bulk.status'), [
            'ids' => $tickets->pluck('id')->toArray(),
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['status']);
    }
}
