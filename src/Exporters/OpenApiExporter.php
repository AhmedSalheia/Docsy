<?php

namespace Ahmedsalheia\Docsy\Exporters;

use Ahmedsalheia\Docsy\DocsyCollection;
use Ahmedsalheia\Docsy\DocsyRequest;
use Ahmedsalheia\Docsy\DocsyFolder;

class OpenApiExporter
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
            'paths' => self::transformContent($collection->content),
        ];
    }

    protected static function transformContent(array $content, string $prefix = ''): array
    {
        $paths = [];

        foreach ($content as $item) {
            if ($item instanceof DocsyFolder) {
                $nested = self::transformContent($item->content, $prefix);
                $paths = array_merge_recursive($paths, $nested);
            } elseif ($item instanceof DocsyRequest) {
                $uri = $prefix . $item->uri;
                $method = strtolower($item->method);

                $paths[$uri][$method] = [
                    'summary' => $uri,
                    'parameters' => self::transformParameters($item->queryParams),
                    'requestBody' => self::transformRequestBody($item),
                    'responses' => [
                        '200' => [
                            'description' => 'Successful response',
                        ]
                    ]
                ];
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
                'in' => $param->in,
                'required' => $param->required,
                'schema' => [
                    'type' => $param->type ?: gettype($param->example)
                ],
                'example' => $param->description
            ];
        }
        return $parameters;
    }

    protected static function transformRequestBody(DocsyRequest $request): array | \stdClass
    {
        if (empty($request->body)) return new \stdClass();

        return [
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => array_map(function ($value) {
                            return ['type' => gettype($value)];
                        }, $request->body),
                        'example' => $request->body
                    ]
                ]
            ]
        ];
    }
}