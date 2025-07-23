<?php

namespace Docsy\Utility\Exporters\openAPI;

use Docsy\Collection;
use Docsy\Folder;
use Docsy\Request;
use Docsy\Utility\Exporters\AbstractExporter;
use Docsy\Utility\Param;
use Docsy\Utility\Variable;
use Exception;
use stdClass;

abstract class OpenApiAbstractExporter extends AbstractExporter
{
    public static array $components = [
        "schemas" => [],
        "parameters" => [],
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

    /**
     * @throws Exception
     */
    protected static function transformCollection(Collection $collection, array $options = []): array
    {
        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => $collection->name,
                'description' => $collection->description,
                'version' => $collection->version,
            ],
            "servers" => self::transformServers($collection->getVariable('base_url')),
            'paths' => array_merge(...self::transformContent($collection->flatten(Request::class))),
            'components' => self::transformComponents(),
        ];
    }
    protected static function transformFolder(Folder $folder, array $options = []): array
    {
        return [];
    }

    /**
     * @throws Exception
     */
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


        if (!empty($requestBody)) {
            $arr_request[$uri][$method]['requestBody'] = $requestBody;
        }

        $arr_request[$uri][$method] = array_merge($arr_request[$uri][$method], self::transformRequestAuth($request->requires_auth));

        return $arr_request;
    }

    protected static function transformServers(Variable|array|null $base_url): array|null
    {
        if (is_null($base_url))
            return null;

        $return = [];
        if (is_a($base_url, Variable::class))
            $return[] = [
                "url" => $base_url->value,
                "description" => $base_url->description
            ];
        else
            foreach ($base_url as $value) {
                $return[] = self::transformServers($value);
            }

        return $return;
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
            dump($param);
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
        return '/' . implode('/', array_map(fn ($path_part) => is_a($path_part,Param::class) ? "{" . $path_part->name . "}" : $path_part, array_values($path)));
    }

    protected static function transformRequestAuth(bool $auth, array $options = []): array
    {
        return $auth ? [
            'security' => [['bearerAuth' => []]]
        ] : [];
    }

    protected static function transformRequestExamples(array $examples, array $options = []): object|array
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
                                '$ref' => $exampleResponseRef,
                            ],
                            "examples" => []
                        ]
                    ]
                ];

            $examplesGroupedByResponseCode[$example->response->code]['content']['application/json']['examples'][$example->name] = [
                'summary' => $example->description,
                "value" => [
                    "status" => $example->response->status,
                    "code" => $example->response->code,
                    "headers" => array_map(fn ($header) => $header[0], $example->response->headers),
                    "body" => $example->response->body,
                ],
            ];
        }

        return !empty($examplesGroupedByResponseCode)? $examplesGroupedByResponseCode : new stdClass();
    }
    protected static function transformComponents() : array
    {
        $return = [];
        foreach (self::$components as $key => $value)
            if (!empty(self::$components[$key]))
                $return[$key] = $value;

        return $return;
    }

    protected static function defineRef(string $ref_name, array $ref_data): string
    {
        if (!isset(self::$components['schemas'][$ref_name]))
            self::$components['schemas'][$ref_name] = [
                "type" => "object",
                "properties" => array_map(fn ($value) => ["type" => gettype($value)] , $ref_data)
            ];
        return "#/components/schemas/$ref_name";
    }

    protected static function transformRequestHeaders(array $headers, array $options): array
    {
        return [];
    }
}