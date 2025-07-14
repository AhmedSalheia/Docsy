<?php

namespace Ahmedsalheia\Docsy\Exporters;

use Ahmedsalheia\Docsy\DocsyCollection;
use Ahmedsalheia\Docsy\DocsyFolder;
use Ahmedsalheia\Docsy\DocsyParam;
use Ahmedsalheia\Docsy\DocsyRequest;
use Ahmedsalheia\Docsy\Enums\ParamLocation;

class PostmanExporter extends AbstractExporter
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
            'item' => self::transformContent($collection->content()),
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
                    'item' => self::transformContent($item->content()),
                ];
            }

            return [];
        }, $content);
    }

    protected static function transformRequest(DocsyRequest $request): array
    {
        if ($request->requires_auth)
            $request->headerParams['Authorization'] = DocsyParam::fromArray([
                'name' => 'Authorization',
                'example' => "{$request->auth_scheme} {$request->auth_token_placeholder}",
                'required' => true,
                 'description' => 'Authorization header',
            ],ParamLocation::Header, $request);

        return [
            'name' => $request->name,
            'description' => $request->description,
            'request' => [
                'method' => strtoupper($request->method->value),
                'header' => array_map(
                    fn($header) => ['key' => $header->name, 'value' => $header->example, 'description' => $header->description],
                    $request->headerParams
                ),
                'url' => self::transformUrl($request->uri, $request->queryParams, $request->pathParams),
                'body' => self::transformBody($request->bodyParams),
            ],
        ];
    }

    protected static function transformUrl(string $uri, array $queryParams, array $urlParams): array
    {
        $cleanUri = ltrim($uri, '/');
        $pathParts = explode('/', $cleanUri);

        // Map urlParams (DocsyParam[]) to variable array
        $variables = array_map(fn($param) => [
            'key' => $param->name,
            'value' => $param->example ?? '',
            'description' => $param->description,
            'required' => $param->required,
        ], $urlParams);

        return [
            'raw' => '{{base_url}}/' . $cleanUri,
            'host' => ['{{base_url}}'],
            'path' => $pathParts,
            'variable' => $variables,
            'query' => array_map(
                fn($param) => [
                    'key' => $param->name,
                    'value' => $param->example,
                    'type' => self::postmanType($param->type ?: gettype($param->example)),
                    'description' => $param->description,
                    'required' => $param->required,
                ],
                $queryParams
            ),
        ];
    }

    protected static function transformBody(array $body): array
    {
        if (empty($body)) {
            return [];
        }

        return [
            'mode' => 'formdata',
            'formdata' => array_map(fn($param) => [
                'key' => $param->name,
                'value' => (string) $param->example,
                'type' => 'text',
                'description' => $param->description,
                'disabled' => !$param->required,
            ], $body),
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
                    'type' => self::postmanType($value['type'] ?? gettype($value['value'])),
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