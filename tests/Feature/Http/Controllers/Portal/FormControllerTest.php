<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Portal;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\IntakeForm;
use App\Models\IntakeFormField;
use App\Models\IntakeFormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormControllerTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private ClientUser $clientUser;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['id' => 1]);

        $this->client = Client::factory()->create();
        $this->clientUser = ClientUser::factory()->create(['client_id' => $this->client->id]);
        $this->actingAs($this->clientUser, 'client');
    }

    public function test_index_shows_available_forms(): void
    {
        IntakeForm::factory()->create(['title' => 'Active Form', 'is_active' => true]);
        IntakeForm::factory()->create(['title' => 'Inactive Form', 'is_active' => false]);

        $response = $this->get(route('portal.intake-forms.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Portal/IntakeForms/Index')
            ->has('forms', 1)
            ->where('forms.0.title', 'Active Form')
        );
    }

    public function test_show_returns_form_fields(): void
    {
        $form = IntakeForm::factory()->create(['is_active' => true]);
        IntakeFormField::factory()->create([
            'intake_form_id' => $form->id,
            'label' => 'Name',
            'field_type' => 'text',
        ]);

        $response = $this->get(route('portal.intake-forms.show', $form));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Portal/IntakeForms/Show')
            ->has('form')
            ->has('form.fields', 1)
        );
    }

    public function test_submit_creates_submission_and_redirects(): void
    {
        $form = IntakeForm::factory()->create(['is_active' => true]);
        $field = IntakeFormField::factory()->create([
            'intake_form_id' => $form->id,
            'label' => 'Name',
            'field_type' => 'text',
            'required' => true,
        ]);

        $response = $this->post(route('portal.intake-forms.submit', $form), [
            'fields' => [
                $field->id => 'John Doe',
            ],
        ]);

        $response->assertRedirect(route('portal.intake-forms.index'));
        $response->assertSessionHas('success', 'Intake form submitted successfully.');
        $this->assertDatabaseHas('intake_form_submission_data', [
            'intake_form_field_id' => $field->id,
            'value' => 'John Doe',
        ]);
    }

    public function test_submit_validates_required_fields(): void
    {
        $form = IntakeForm::factory()->create(['is_active' => true]);
        IntakeFormField::factory()->create([
            'intake_form_id' => $form->id,
            'label' => 'Required Field',
            'field_type' => 'text',
            'required' => true,
        ]);

        $response = $this->post(route('portal.intake-forms.submit', $form), [
            'fields' => [],
        ]);

        $response->assertSessionHasErrors('fields.' . IntakeFormField::first()->id);
    }

    public function test_submit_handles_file_upload(): void
    {
        Storage::fake('local');
        $form = IntakeForm::factory()->create(['is_active' => true]);
        $field = IntakeFormField::factory()->create([
            'intake_form_id' => $form->id,
            'label' => 'Resume',
            'field_type' => 'file',
            'required' => true,
        ]);
        $file = UploadedFile::fake()->create('resume.pdf', 100);

        $response = $this->post(route('portal.intake-forms.submit', $form), [
            'fields' => [
                $field->id => $file,
            ],
        ]);

        $response->assertRedirect(route('portal.intake-forms.index'));
        $this->assertDatabaseHas('intake_form_submission_data', [
            'intake_form_field_id' => $field->id,
            'value' => 'resume.pdf',
        ]);
        Storage::disk('local')->assertExists(IntakeFormSubmission::first()->data->first()->file_path);
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post(route('portal.logout'));

        $this->get(route('portal.intake-forms.index'))->assertRedirect(route('portal.login'));
    }
}
