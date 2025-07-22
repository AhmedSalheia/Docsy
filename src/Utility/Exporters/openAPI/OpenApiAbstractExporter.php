<?php

namespace Docsy\Utility\Exporters\openAPI;

use Docsy\Collection;
use Docsy\Folder;
use Docsy\Request;
use Docsy\Utility\Exporters\AbstractExporter;
use Docsy\Utility\Param;

abstract class OpenApiAbstractExporter extends AbstractExporter
{
    public static array $components = [
        "schemas" => [],
        "parameters" => [],
        "requestBody" => [],
        "responses" => [],
        "headers" => [],
        'securitySchemes' => [
            'bearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT',
            ],
        ]
    ];
    protected static function transformCollection(Collection $collection, array $options = []): array
    {
        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => $collection->name,
                'description' => $collection->description,
                'version' => $collection->version,
            ],
            'paths' => self::transformContent($collection->content()),
            'components' => self::transformComponents(),
        ];
    }
    protected static function transformFolder(Folder $folder, array $options = []): array
    {
        return self::transformContent($folder->content());
    }

    protected static function transformRequest(Request $request, array $options = []): array
    {
        $arr_request = [];
        $uri = self::transformRequestUrl($request->getBaseUrl(), $request->path);

        $method = strtolower($request->method->value);

        $requestBody = self::transformRequestBody($request->bodyParams);

        $arr_request[$uri][$method] = [
            'summary' => $request->name ?: $uri,
            'description' => $request->description,
            'parameters' => self::transformParameters(array_merge($request->pathParams, $request->queryParams, $request->headerParams)),
            'responses' => self::transformRequestExamples($request->examples),
        ];


        if (empty($requestBody)) {
            $arr_request[$uri][$method]['requestBody'] = $requestBody;
        }

        $arr_request[$uri][$method] = array_merge($arr_request[$uri][$method], self::transformRequestAuth($request->requires_auth));

        return $arr_request;
    }

    protected static function transformParameters(array $params): array
    {
        $parameters = [];
        foreach ($params as $param) {
            $parameters[] = [
                'name' => $param->name,
                'in' => $param->in->value, // Serialize enum to string
                'required' => $param->required,
                'schema' => [
                    'type' => $param->type ?: gettype($param->example),
                ],
                'description' => $param->description,
                'example' => $param->value,
            ];
        }
        return $parameters;
    }

    protected static function transformRequestBody(array $body, array $options = []): array
    {
        if (empty($body)) return [];

        $properties = [];
        $required = [];

        foreach ($body as $param) {
            $properties[$param->name] = [
                'type' => $param->type ?: gettype($param->value),
                'description' => $param->description,
                'example' => $param->value,
            ];
            if ($param->required) {
                $required[] = $param->name;
            }
        }

        $schema = [
            'type' => 'object',
            'properties' => $properties,
        ];

        if (!empty($required)) {
            $schema['required'] = $required;
        }

        return [
            'content' => [
                'application/json' => [
                    'schema' => $schema,
                ],
            ],
        ];
    }

    protected static function transformVariables(array $variables, array $options = []): array
    {
        return [];
    }

    protected static function transformRequestUrl(string $base_url, array $path, array $queryParams = [], array $pathParams = [], array $options = []): array | string
    {
        return array_map(fn ($path_part) => is_a($path_part,Param::class) ? "{" . $path_part->name . "}" : $path_part, $path);
    }

    protected static function transformRequestAuth(bool $auth, array $options = []): array
    {
        return $auth ? [
            'security' => [['bearerAuth' => []]]
        ] : [];
    }

    protected static function transformRequestExamples(array $examples, array $options = []): array
    {
        $examplesGroupedByResponseCode = [];

        $exampleResponseRef = self::defineRef('ExampleResponse', [
            "status" => "string",
            "code" => "integer",
            "headers" => "array",
            "body" => "object"
        ]);

        foreach ($examples as $example) {
            if (!isset($examplesGroupedByResponseCode[$example->response->code]))
                $examplesGroupedByResponseCode[$example->response->code] = [
                    "description" => $example->response->status,
                    "content" => [
                        "application/json" => [
                            "schema" => [
                                'ref' => $exampleResponseRef,
                            ],
                            "examples" => []
                        ]
                    ]
                ];

            $examplesGroupedByResponseCode[$example->response->code]['content']['application/json']['examples'][$example->name] = [
                'summary' => $example->description,
                "value" => [
                    "status" => $example->response->status,
                    "headers" => array_map(fn ($header) => $header[0], $example->response->headers),
                    "body" => $example->response->body,
                ],
            ];
        }

        return $examplesGroupedByResponseCode;
    }
    protected static function transformComponents() : array
    {
        return self::$components;
    }

    protected static function defineRef(string $ref_name, array $ref_data): string
    {
        if (!isset(self::$components['schemas'][$ref_name]))
            self::$components['schemas'][$ref_name] = [
                "type" => "object",
                "properties" => array_map(fn ($value) => ["type" => gettype($value)] , $ref_data)
            ];
        return "#/components/schemas/{$ref_name}";
    }

    protected static function transformRequestHeaders(array $headers, array $options): array
    {
        return [];
    }
}