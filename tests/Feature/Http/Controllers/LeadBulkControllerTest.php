<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadBulkControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['id' => 1]);
        $this->actingAs($user);
    }

    public function test_bulk_delete_removes_selected_leads(): void
    {
        $leads = Lead::factory()->count(3)->create();

        $response = $this->post(route('leads.bulk.delete'), [
            'ids' => $leads->pluck('id')->toArray(),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('leads', ['id' => $leads[0]->id]);
        $this->assertDatabaseMissing('leads', ['id' => $leads[1]->id]);
        $this->assertDatabaseMissing('leads', ['id' => $leads[2]->id]);
    }

    public function test_bulk_delete_validates_empty_ids(): void
    {
        $response = $this->post(route('leads.bulk.delete'), [
            'ids' => [],
        ]);

        $response->assertSessionHasErrors(['ids']);
    }

    public function test_bulk_delete_validates_nonexistent_ids(): void
    {
        $response = $this->post(route('leads.bulk.delete'), [
            'ids' => ['non-existent-id'],
        ]);

        $response->assertSessionHasErrors(['ids.0']);
    }

    public function test_bulk_update_status_changes_selected_leads(): void
    {
        $leads = Lead::factory()->count(3)->create(['status' => 'new']);

        $response = $this->post(route('leads.bulk.status'), [
            'ids' => $leads->pluck('id')->toArray(),
            'status' => 'contacted',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('contacted', $leads[0]->fresh()->status->value);
        $this->assertEquals('contacted', $leads[1]->fresh()->status->value);
        $this->assertEquals('contacted', $leads[2]->fresh()->status->value);
    }

    public function test_bulk_update_status_validates_invalid_status(): void
    {
        $leads = Lead::factory()->count(2)->create();

        $response = $this->post(route('leads.bulk.status'), [
            'ids' => $leads->pluck('id')->toArray(),
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    public function test_bulk_email_sends_to_selected_leads(): void
    {
        config(['mail.from.address' => 'noreply@plazaos.test']);

        $leads = Lead::factory()->count(3)->create(['email' => 'lead@example.com']);

        $response = $this->post(route('leads.bulk.email'), [
            'ids' => $leads->pluck('id')->toArray(),
            'subject' => 'Bulk Test',
            'body' => 'Hello {{contact_name}}',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('emails', ['subject' => 'Bulk Test']);
    }

    public function test_bulk_email_validates_required_fields(): void
    {
        $leads = Lead::factory()->count(2)->create();

        $response = $this->post(route('leads.bulk.email'), [
            'ids' => $leads->pluck('id')->toArray(),
        ]);

        $response->assertSessionHasErrors(['subject', 'body']);
    }

    public function test_bulk_email_skips_leads_without_email(): void
    {
        config(['mail.from.address' => 'noreply@plazaos.test']);

        $withEmail = Lead::factory()->create(['email' => 'has@email.com']);
        $withoutEmail = Lead::factory()->create(['email' => null]);

        $response = $this->post(route('leads.bulk.email'), [
            'ids' => [$withEmail->id, $withoutEmail->id],
            'subject' => 'Bulk Test',
            'body' => 'Body',
        ]);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('1 lead(s)', session('success'));

        $this->assertDatabaseHas('emails', [
            'emailable_id' => $withEmail->id,
            'subject' => 'Bulk Test',
        ]);
    }
}
