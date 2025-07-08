<?php

namespace Ahmedsalheia\Docsy\Exporters;

use Ahmedsalheia\Docsy\DocsyCollection;
use Ahmedsalheia\Docsy\DocsyFolder;
use Ahmedsalheia\Docsy\DocsyRequest;
class PostmanExporter
{
    public static function export(DocsyCollection $collection): array
    {
        return [
            'info' => [
                'name' => $collection->name,
                'description' => $collection->description,
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
                'version' => $collection->version,
            ],
            'variable' => self::transformVariables($collection->getVariables()),
            'item' => self::transformContent($collection->content),
        ];
    }

    protected static function transformContent(array $content): array
    {
        return array_map(function ($item) {
            if ($item instanceof DocsyRequest) {
                return self::transformRequest($item);
            }

            if ($item instanceof DocsyFolder) {
                return [
                    'name' => $item->name,
                    'description' => $item->description,
                    'item' => self::transformContent($item->content),
                ];
            }

            return [];
        }, $content);
    }

    protected static function transformRequest(DocsyRequest $request): array
    {
        return [
            'name' => $request->name,
            'description' => $request->description,
            'request' => [
                'method' => strtoupper($request->method),
                'header' => array_map(fn($k, $v) => ['key' => $k, 'value' => $v], array_keys($request->headers), $request->headers),
                'url' => self::transformUrl($request->uri, $request->queryParams),
                'body' => self::transformBody($request->body),
            ],
        ];
    }

    protected static function transformUrl(string $uri, array $params): array
    {
        $cleanUri = ltrim($uri, '/');
        return [
            'raw' => '{{base_url}}/' . $cleanUri,
            'host' => ['{{base_url}}'],
            'path' => explode('/', $cleanUri),
            'query' => array_map(
                fn($param) => [
                    'key' => $param->name,
                    'value' => $param->example,
                    'type' => self::postmanType($param->type ?: gettype($param->example)),
                    'description' => $param->description,
                    'required' => $param->required,
                ]
                , $params),
        ];
    }

    protected static function transformBody(array $body): array
    {
        if (empty($body)) return [];

        return [
            'mode' => 'raw',
            'raw' => json_encode($body, JSON_PRETTY_PRINT),
            'options' => [
                'raw' => [
                    'language' => 'json',
                ],
            ],
        ];
    }

    protected static function transformVariables(array $variables): array
    {
        return array_map(function ($key, $value) {
            if (is_array($value)) {
                if (!array_key_exists('value', $value)) {
                    throw new \InvalidArgumentException("Variable '{$key}' is missing required 'value' key.");
                }

                return [
                    'key' => $key,
                    'value' => $value['value'],
                    'type' => self::postmanType(gettype($value['type'] ?? $value['value'])),
                    'description' => $value['description'] ?? '',
                ];
            }

            // Simple value, infer type
            return [
                'key' => $key,
                'value' => $value,
                'type' => self::postmanType(gettype($value)),
                'description' => '',
            ];
        }, array_keys($variables), $variables);
    }

    protected static function postmanType($type): string
    {
        $type = strtolower($type);
        return match ($type) {
            'boolean', 'bool' => 'boolean',
            'integer', 'int', 'float', 'double' => 'number',
            'secret' => 'secret',
            default => 'string',
        };
    }
}