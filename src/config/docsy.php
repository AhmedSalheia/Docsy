<?php

return [
    'default_collection' => [
        "name" => env('DOCSY_DEFAULT_COLLECTION_NAME', 'default_collection'),
        "description" => env('DOCSY_DEFAULT_COLLECTION_DESC', 'This is Default Collection'),
        "version" => env('DOCSY_DEFAULT_COLLECTION_VERSION', '1.0.0'),
    ],
    'ui_path' => env('DOCSY_URL', '/docs'),
    'examples_path' => env('DOCSY_EXAMPLES_PATH', __DIR__ . '/../../examples'),
    'base_url' => env('DOCSY_URL', 'http://slimapp.test'),
    "export_path" => env('DOCSY_EXPORT_PATH', __DIR__ . '/../../exports'),
];
