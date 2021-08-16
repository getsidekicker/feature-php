<?php

return [
    'flagr_url' => env('FEATURE_FLAGR_URL', 'http://localhost:18000'),
    // timeouts in seconds
    'connect_timeout' => env('FEATURE_CONNECT_TIMEOUT', 1),
    'timeout' => env('FEATURE_TIMEOUT', 1),
    // auth scheme for Create Flag operations
    // 'none' or 'basic' are valid
    'auth' => env('FEATURE_AUTH', "none"),
    'basic' => [
        'username' => env('FEATURE_AUTH_BASIC_USERNAME'),
        'password' => env('FEATURE_AUTH_BASIC_PASSWORD'),
    ],
    // tags
    'tag_operator' => 'ANY',
    'tags' => []
];
