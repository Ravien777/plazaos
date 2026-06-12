<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlanSlug;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'slug' => PlanSlug::Free->value,
            'name' => 'Free',
            'description' => 'For solo operators getting started',
            'monthly_price_cents' => 0,
            'max_users' => 1,
            'features' => [
                'leads', 'clients', 'projects', 'documents', 'notes',
            ],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Plan::create([
            'slug' => PlanSlug::Pro->value,
            'name' => 'Pro',
            'description' => 'For small teams up to 5 users',
            'monthly_price_cents' => 4900,
            'max_users' => 5,
            'features' => [
                'leads', 'clients', 'projects', 'documents', 'notes',
                'email', 'meetings', 'webhooks', 'ai', 'client_portal',
                'csv_import', 'tickets',
            ],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Plan::create([
            'slug' => PlanSlug::Team->value,
            'name' => 'Team',
            'description' => 'For growing teams up to 20 users',
            'monthly_price_cents' => 14900,
            'max_users' => 20,
            'features' => [
                'leads', 'clients', 'projects', 'documents', 'notes',
                'email', 'meetings', 'webhooks', 'ai', 'client_portal',
                'csv_import', 'tickets', 'api', 'integrations',
                'testimonials', 'intake_forms',
            ],
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }
}
