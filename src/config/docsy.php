<?php

return [
    'default_collection' => [
        "name" => env('DOCSY_DEFAULT_COLLECTION_NAME', 'default_collection'),
        "description" => env('DOCSY_DEFAULT_COLLECTION_DESC', 'This is Default Collection'),
        "version" => env('DOCSY_DEFAULT_COLLECTION_VERSION', '1.0.0'),
    ],
    'ui_path' => env('DOCSY_URL', '/docs'),
    'examples_path' => env('DOCSY_EXAMPLES_PATH', __DIR__ . '/../../cache/examples'),
    'base_url' => env('DOCSY_URL', 'http://slimapp.test'),
    "export_path" => env('DOCSY_EXPORT_PATH', __DIR__ . '/../../exports'),
    "auth" => [
        "scheme" => env('DOCSY_AUTH_SCHEME','bearer'),
        "default_credentials" => [
            "username" => env('DOCSY_DEFAULT_AUTH_USERNAME'),
            "password" => env('DOCSY_DEFAULT_AUTH_PASSWORD')
        ],
        "token_path" => env('DOCSY_TOKEN_PATH',"data.path"),
        "auto_run" => env('DOCSY_AUTO_RUN',true),
    ],

    "formatters" => [
        "exporters" => [
            "json" => \Docsy\Exporters\JsonExporter::class,
            "postman" => \Docsy\Exporters\PostmanExporter::class,
            "openapi.json" => \Docsy\Exporters\openAPI\JsonExporter::class,
            "openapi.yaml" => \Docsy\Exporters\openAPI\YamlExporter::class,
            "openapi.yml" => \Docsy\Exporters\openAPI\YamlExporter::class,
        ],
        "importers" => [
            "json" => \Docsy\Importers\JsonImporter::class,
        ]
    ]
];
