<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Client;
use App\Models\IntakeForm;
use App\Models\IntakeFormField;
use App\Models\IntakeFormSubmission;
use App\Models\IntakeFormSubmissionData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IntakeFormSubmissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_show_returns_200(): void
    {
        $form = IntakeForm::factory()->create();
        $client = Client::factory()->create();
        $field = IntakeFormField::factory()->create(['intake_form_id' => $form->id]);
        $submission = IntakeFormSubmission::factory()->create([
            'intake_form_id' => $form->id,
            'client_id' => $client->id,
        ]);
        IntakeFormSubmissionData::factory()->create([
            'intake_form_submission_id' => $submission->id,
            'intake_form_field_id' => $field->id,
        ]);

        $response = $this->get(route('intake-forms.submissions.show', [$form, $submission]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('IntakeForms/Submission'));
    }

    public function test_download_returns_file(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('document.pdf', 100);
        $path = $file->store('intake-forms/test', 'local');

        $response = $this->get(route('intake-forms.submissions.download', ['path' => $path]));

        $response->assertOk();
    }

    public function test_download_returns_404_when_file_missing(): void
    {
        $response = $this->get(route('intake-forms.submissions.download', ['path' => 'nonexistent/file.pdf']));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'File not found.');
    }
}
