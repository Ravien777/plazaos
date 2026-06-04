<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\IntakeForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntakeFormControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['id' => 1]);
        $this->actingAs($user);
    }

    public function test_index_returns_200(): void
    {
        IntakeForm::factory()->count(3)->create();

        $response = $this->get(route('intake-forms.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('IntakeForms/Index'));
    }

    public function test_create_returns_200(): void
    {
        $response = $this->get(route('intake-forms.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('IntakeForms/Create'));
    }

    public function test_store_creates_form_with_fields_and_redirects(): void
    {
        $data = [
            'title' => 'Client Onboarding Form',
            'description' => 'Collect initial info',
            'is_active' => true,
            'fields' => [
                ['label' => 'Full Name', 'field_type' => 'text', 'required' => true, 'sort_order' => 0],
                ['label' => 'Company', 'field_type' => 'text', 'required' => false, 'sort_order' => 1],
            ],
        ];

        $response = $this->post(route('intake-forms.store'), $data);

        $response->assertRedirect(route('intake-forms.index'));
        $this->assertDatabaseHas('intake_forms', ['title' => 'Client Onboarding Form']);
        $this->assertDatabaseHas('intake_form_fields', ['label' => 'Full Name']);
        $this->assertDatabaseCount('intake_form_fields', 2);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post(route('intake-forms.store'), []);

        $response->assertSessionHasErrors(['title', 'fields']);
    }

    public function test_show_returns_200_with_relations(): void
    {
        $form = IntakeForm::factory()->hasFields(2)->create();

        $response = $this->get(route('intake-forms.show', $form));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('IntakeForms/Show')
            ->has('form')
        );
    }

    public function test_edit_returns_200(): void
    {
        $form = IntakeForm::factory()->hasFields(2)->create();

        $response = $this->get(route('intake-forms.edit', $form));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('IntakeForms/Edit'));
    }

    public function test_update_updates_form_and_fields(): void
    {
        $form = IntakeForm::factory()->hasFields(1)->create();

        $response = $this->put(route('intake-forms.update', $form), [
            'title' => 'Updated Form',
            'description' => 'Updated description',
            'is_active' => false,
            'fields' => [
                ['label' => 'New Field', 'field_type' => 'email', 'required' => true, 'sort_order' => 0],
            ],
        ]);

        $response->assertRedirect(route('intake-forms.index'));
        $this->assertEquals('Updated Form', $form->fresh()->title);
        $this->assertFalse($form->fresh()->is_active);
        $this->assertEquals('New Field', $form->fresh()->fields()->first()->label);
        $this->assertDatabaseCount('intake_form_fields', 1);
    }

    public function test_destroy_deletes_form_and_redirects(): void
    {
        $form = IntakeForm::factory()->create();

        $response = $this->delete(route('intake-forms.destroy', $form));

        $response->assertRedirect(route('intake-forms.index'));
        $this->assertDatabaseMissing('intake_forms', ['id' => $form->id]);
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->get(route('intake-forms.index'))->assertRedirect(route('login'));
    }
}
