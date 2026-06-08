<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadConversionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_converts_lead_to_client(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->post(route('leads.convert', $lead));

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', ['lead_id' => $lead->id]);
    }

    public function test_prevents_double_conversion(): void
    {
        $lead = Lead::factory()->won()->create();

        $response = $this->post(route('leads.convert', $lead));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_guest_redirects_to_login(): void
    {
        $lead = Lead::factory()->create();

        $this->post('/logout');
        $response = $this->post(route('leads.convert', $lead));

        $response->assertRedirect(route('login'));
    }
}
