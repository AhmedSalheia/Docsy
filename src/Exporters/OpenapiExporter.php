<?php

namespace Ahmedsalheia\Docsy\Exporters;

use Ahmedsalheia\Docsy\DocsyCollection;
use Ahmedsalheia\Docsy\DocsyRequest;
use Ahmedsalheia\Docsy\DocsyFolder;

class OpenapiExporter extends AbstractExporter
{
    public static function export(DocsyCollection $collection): array
    {
        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => $collection->name,
                'description' => $collection->description,
                'version' => $collection->version,
            ],
            'paths' => self::transformContent($collection->content()),
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                    ],
                ],
            ],
        ];
    }

    protected static function transformContent(array $content, string $prefix = ''): array
    {
        $paths = [];

        foreach ($content as $item) {

            if ($item instanceof DocsyFolder) {

                $nested = self::transformContent($item->content(), $prefix);
                $paths = array_merge_recursive($paths, $nested);

            } elseif ($item instanceof DocsyRequest) {

                $uri = $prefix . $item->uri;

                $method = strtolower($item->method->value);

                $requestBody = self::transformRequestBody($item);

                $paths[$uri][$method] = [
                    'summary' => $item->name ?: $uri,
                    'description' => $item->description,
                    'parameters' => self::transformParameters(array_merge($item->pathParams, $item->queryParams)),
                    'responses' => [
                        '200' => [
                            'description' => 'Successful response',
                        ]
                    ],
                ];


                if ($requestBody !== null) {
                    $paths[$uri][$method]['requestBody'] = $requestBody;
                }

                if ($item->requires_auth) {
                    $paths[$uri][$method]['security'] = [['bearerAuth' => []]];
                }
            }
        }

        return $paths;
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
                'example' => $param->example,
            ];
        }
        return $parameters;
    }

    protected static function transformRequestBody(DocsyRequest $request): array|null
    {
        if (empty($request->bodyParams)) return null;

        $properties = [];
        $required = [];

        foreach ($request->bodyParams as $param) {
            $properties[$param->name] = [
                'type' => $param->type ?: gettype($param->example),
                'description' => $param->description,
                'example' => $param->example,
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
}