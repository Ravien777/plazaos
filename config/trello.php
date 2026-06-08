<?php

declare(strict_types=1);

return [
    'api_key' => env('TRELLO_API_KEY'),
    'api_token' => env('TRELLO_API_TOKEN'),

    'boards' => [
        'low_medium' => env('TRELLO_BOARD_LOW_MEDIUM'),
        'high' => env('TRELLO_BOARD_HIGH'),
        'today' => env('TRELLO_BOARD_TODAY'),
        'this_week' => env('TRELLO_BOARD_THIS_WEEK'),
        'this_month' => env('TRELLO_BOARD_THIS_MONTH'),
        'later' => env('TRELLO_BOARD_LATER'),
    ],
];
