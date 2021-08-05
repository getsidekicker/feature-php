<?php

return [
    'flagr_url' => env('FEATURE_FLAGR_URL', 'http://localhost:18000'),
    // timeouts in seconds
    'connect_timeout' => env('FEATURE_CONNECT_TIMEOUT', 1),
    'timeout' => env('FEATURE_TIMEOUT', 1)
];
