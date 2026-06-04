<?php

declare(strict_types=1);

use App\Services\ActivityService;

if (!function_exists('activity')) {
    function activity(): ActivityService
    {
        return app(ActivityService::class);
    }
}
