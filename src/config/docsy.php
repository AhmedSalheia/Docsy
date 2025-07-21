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
            "username" => env('DOCSY_DEFAULT_AUTH_USERNAME','user@slimapp.com'),
            "password" => env('DOCSY_DEFAULT_AUTH_PASSWORD','1234')
        ],
        "token_variable_name" => env('DOCSY_TOKEN_VARIABLE_NAME','access_token'),
        "token_path" => env('DOCSY_TOKEN_PATH',"data.access_token"),
        "auto_run" => env('DOCSY_AUTO_RUN',true),
    ],

    "formatters" => [
        "exporters" => [
            "json" => \Docsy\Utility\Exporters\JsonExporter::class,
            "postman" => \Docsy\Utility\Exporters\PostmanExporter::class,
            "openapi.json" => \Docsy\Utility\Exporters\openAPI\JsonExporter::class,
            "openapi.yaml" => \Docsy\Utility\Exporters\openAPI\YamlExporter::class,
            "openapi.yml" => \Docsy\Utility\Exporters\openAPI\YamlExporter::class,
        ],
        "importers" => [
            "json" => \Docsy\Utility\Importers\JsonImporter::class,
        ]
    ]
];
