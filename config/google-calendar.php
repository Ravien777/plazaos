<?php

declare(strict_types=1);

return [
    'enabled' => env('GOOGLE_CALENDAR_ENABLED', false),

    'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),

    'credentials_path' => env('GOOGLE_CALENDAR_CREDENTIALS_PATH', storage_path('app/google-calendar-credentials.json')),
];
