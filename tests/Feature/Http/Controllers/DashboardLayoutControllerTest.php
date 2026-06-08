<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardLayoutControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_saves_layout(): void
    {
        $this->post(route('dashboard.layout.update'), [
            'stat_cards' => ['stat-new-leads', 'stat-active-leads'],
            'bottom_widgets' => ['meetings', 'activity'],
        ])->assertRedirect();

        $this->assertSame([
            'stat_cards' => ['stat-new-leads', 'stat-active-leads'],
            'bottom_widgets' => ['meetings', 'activity'],
        ], User::first()->dashboard_layout);
    }

    public function test_rejects_invalid_stat_card_id(): void
    {
        $this->post(route('dashboard.layout.update'), [
            'stat_cards' => ['stat-invalid'],
            'bottom_widgets' => ['meetings'],
        ])->assertSessionHasErrors('stat_cards.0');
    }

    public function test_rejects_invalid_bottom_widget_id(): void
    {
        $this->post(route('dashboard.layout.update'), [
            'stat_cards' => ['stat-new-leads'],
            'bottom_widgets' => ['invalid-widget'],
        ])->assertSessionHasErrors('bottom_widgets.0');
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->post('/logout');

        $this->post(route('dashboard.layout.update'), [
            'stat_cards' => ['stat-new-leads'],
            'bottom_widgets' => ['meetings'],
        ])->assertRedirect(route('login'));
    }
}
