<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailTemplateControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_index_returns_200(): void
    {
        EmailTemplate::create(['key' => 'welcome', 'name' => 'Welcome', 'subject' => 'Welcome!', 'body' => 'Hello.']);
        EmailTemplate::create(['key' => 'followup', 'name' => 'Follow Up', 'subject' => 'Follow Up', 'body' => 'Following up.']);
        EmailTemplate::create(['key' => 'goodbye', 'name' => 'Goodbye', 'subject' => 'Goodbye', 'body' => 'Bye.']);

        $response = $this->get(route('templates.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Templates/Index')
            ->has('templates', 3)
        );
    }

    public function test_create_returns_200(): void
    {
        $response = $this->get(route('templates.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Templates/Create'));
    }

    public function test_store_creates_template(): void
    {
        $response = $this->post(route('templates.store'), [
            'key' => 'test_template',
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body' => 'Test body content.',
        ]);

        $response->assertRedirect(route('templates.index'));
        $this->assertDatabaseHas('email_templates', ['key' => 'test_template']);
    }

    public function test_store_validates_required(): void
    {
        $response = $this->post(route('templates.store'), []);

        $response->assertSessionHasErrors(['key', 'name', 'subject', 'body']);
    }

    public function test_store_validates_unique_key(): void
    {
        EmailTemplate::create(['key' => 'duplicate', 'name' => 'Original', 'subject' => 'Subject', 'body' => 'Body.']);

        $response = $this->post(route('templates.store'), [
            'key' => 'duplicate',
            'name' => 'Copy',
            'subject' => 'Copy Subject',
            'body' => 'Copy body.',
        ]);

        $response->assertSessionHasErrors(['key']);
    }

    public function test_edit_returns_200(): void
    {
        $template = EmailTemplate::create(['key' => 'edit_me', 'name' => 'Edit Me', 'subject' => 'Subject', 'body' => 'Body.']);

        $response = $this->get(route('templates.edit', $template));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Templates/Edit')
            ->has('template')
        );
    }

    public function test_update_updates_template(): void
    {
        $template = EmailTemplate::create(['key' => 'update_me', 'name' => 'Old Name', 'subject' => 'Subject', 'body' => 'Body.']);

        $response = $this->put(route('templates.update', $template), [
            'key' => 'update_me',
            'name' => 'Updated Name',
            'subject' => 'Updated Subject',
            'body' => 'Updated body.',
        ]);

        $response->assertRedirect(route('templates.index'));
        $this->assertEquals('Updated Name', $template->fresh()->name);
    }

    public function test_destroy_deletes_template(): void
    {
        $template = EmailTemplate::create(['key' => 'delete_me', 'name' => 'Delete Me', 'subject' => 'Subject', 'body' => 'Body.']);

        $response = $this->delete(route('templates.destroy', $template));

        $response->assertRedirect(route('templates.index'));
        $this->assertSoftDeleted($template);
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->get(route('templates.index'))->assertRedirect(route('login'));
    }
}
