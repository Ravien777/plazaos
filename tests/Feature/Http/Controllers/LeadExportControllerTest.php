<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadExportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_export_returns_csv(): void
    {
        Lead::factory()->count(3)->create();

        $response = $this->get(route('leads.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('leads-export-', $response->headers->get('Content-Disposition'));
    }

    public function test_export_filters_by_status(): void
    {
        Lead::factory()->create(['status' => 'new', 'company_name' => 'NewCo']);
        Lead::factory()->create(['status' => 'qualified', 'company_name' => 'QualCo']);

        $response = $this->get(route('leads.export', ['status' => 'new']));

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('NewCo', $content);
        $this->assertStringNotContainsString('QualCo', $content);
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->get(route('leads.export'))->assertRedirect(route('login'));
    }
}
