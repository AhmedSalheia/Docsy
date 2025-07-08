<?php

return [
    'default_collection' => [
        "name" => env('DOCSY_DEFAULT_COLLECTION_NAME', 'default_collection'),
        "description" => env('DOCSY_DEFAULT_COLLECTION_DESC', 'This is Default Collection'),
        "version" => env('DOCSY_DEFAULT_COLLECTION_VERSION', '1.0.0'),
    ],
    'url' => env('DOCSY_URL', '/docs'),
];
