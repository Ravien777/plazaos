<?php

declare(strict_types=1);

return [

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'trial_days' => (int) env('STRIPE_TRIAL_DAYS', 14),
    ],

    'plans' => [
        'free' => [
            'max_users' => 1,
            'features' => [
                'leads', 'clients', 'projects', 'documents', 'notes',
            ],
        ],
        'pro' => [
            'price_id' => env('STRIPE_PRO_PRICE_ID'),
            'max_users' => 5,
            'features' => [
                'leads', 'clients', 'projects', 'documents', 'notes',
                'email', 'meetings', 'webhooks', 'ai', 'client_portal',
                'csv_import', 'tickets',
            ],
        ],
        'team' => [
            'price_id' => env('STRIPE_TEAM_PRICE_ID'),
            'max_users' => 20,
            'features' => [
                'leads', 'clients', 'projects', 'documents', 'notes',
                'email', 'meetings', 'webhooks', 'ai', 'client_portal',
                'csv_import', 'tickets', 'api', 'integrations',
                'testimonials', 'intake_forms',
            ],
        ],
    ],

];
