<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\LeadImport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LeadImportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['id' => 1]);
        $this->actingAs($user);
    }

    public function test_create_returns_200(): void
    {
        $response = $this->get(route('leads.import.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Leads/Import'));
    }

    public function test_import_creates_import_and_redirects(): void
    {
        $csvContent = "company_name,email\nTestCorp,test@test.com\n";
        $file = UploadedFile::fake()->createWithContent('leads.csv', $csvContent);

        $response = $this->post(route('leads.import.store'), [
            'file' => $file,
            'column_mapping' => json_encode(['company_name' => 'company_name', 'email' => 'email']),
            'duplicate_strategy' => 'skip',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $import = LeadImport::first();
        $this->assertNotNull($import, 'LeadImport should be created');
    }

    public function test_progress_returns_json(): void
    {
        $import = LeadImport::factory()->create();

        $response = $this->get("/leads/import/{$import->id}/progress");

        $response->assertOk();
        $response->assertJsonStructure(['id', 'status', 'total_rows']);
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->get(route('leads.import.create'))->assertRedirect(route('login'));
    }
}
