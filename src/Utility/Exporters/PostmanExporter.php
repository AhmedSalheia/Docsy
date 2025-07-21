<?php

namespace Docsy\Utility\Exporters;

use Docsy\Collection;
use Docsy\Docsy;
use Docsy\Folder;
use Docsy\Request;
use Docsy\Utility\Example;
use Docsy\Utility\Param;
use Docsy\Utility\Variable;

class PostmanExporter extends AbstractExporter
{
    public static function export(Docsy $docsy, string $collection = "",array $options = []): string
    {
        if ($collection == "")
            throw new \InvalidArgumentException('Postman Exporter only meant to export single collection at a time');

        return json_encode(self::transformCollection($docsy->getCollection($collection)),JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected static function transformCollection(Collection $collection): array
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

    protected static function transformFolder(Folder $folder, array $options = []): array
    {
        return [
            'name' => $folder->name,
            'description' => $folder->description,
            'item' => self::transformContent($folder->content()),
        ];
    }

    protected static function transformRequest(Request $request, array $options = []): array
    {
        return [
            'name' => $request->name,
            'description' => $request->description,
            'request' => [
                'method' => strtoupper($request->method->value),
                'header' => self::transformRequestHeaders($request->headerParams),
                'url' => self::transformRequestUrl($request->getBaseUrl(), $request->path, $request->queryParams, $request->pathParams),
                'body' => self::transformRequestBody($request->bodyParams),
                "auth" => self::transformRequestAuth($request->requires_auth),
                "responses" => self::transformRequestExamples($request->examples)
            ],
        ];
    }

    protected static function transformRequestUrl(string $base_url, array $path, array $queryParams = [], array $pathParams = [], array $options = []): array
    {
        $variables = array_map(fn($param) => [
            'key' => $param->name,
            'value' => $param->value ?? '',
            'description' => $param->description,
            'required' => $param->required,
        ], $pathParams);

        $path = array_map(
            fn ($path_part) => is_a($path_part, Param::class) ? ":".$path_part->name : $path_part,
            $path
        );

        $base_url = preg_split('/:\\\\/', $base_url,1);

        return [
            'raw' => implode('/', [$base_url, ...$path]),
            "scheme" => $base_url[0],
            'host' => explode('.', $base_url[1]),
            'path' => $path,
            'variable' => $variables,
            'query' => array_map(
                fn($param) => [
                    'key' => $param->name,
                    'value' => $param->value,
                    'type' => self::postmanType($param->type ?: gettype($param->value)),
                    'description' => $param->description,
                    'required' => $param->required,
                ],
                $queryParams
            )
        ];
    }

    protected static function transformRequestHeaders(array $headers, array $options): array
    {
        if (empty($headers)) {
            return [];
        }

        return array_map(
            fn($header) => ['key' => $header->name, 'value' => $header->value, 'description' => $header->description],
            $headers
        );
    }

    protected static function transformRequestBody(array $body, array $options = []): array
    {
        if (empty($body)) {
            return [];
        }

        return [
            'mode' => 'formdata',
            'formdata' => array_map(fn($param) => [
                'key' => $param->name,
                'value' => (string) $param->value,
                'type' => 'text',
                'description' => $param->description,
                'disabled' => !$param->required,
            ], $body),
        ];
    }

    protected static function transformRequestAuth(bool $auth, array $options = []): array
    {
        return $auth ? [
            "type" => "bearer",
            "bearer" => [
                "key" => "token",
                "value" => "{{token}}"
            ]
        ] : [];
    }

    protected static function transformRequestExamples(array $examples, array $options = []): array
    {
        return array_map(fn(Example $example) => [
            "name" => $example->name,
            "originalRequest" => self::transformRequest($example->request),
            "status" => $example->response->status,
            "code" => $example->response->status_code,
            "headers" => $example->response->headers,
            "body" => json_encode($example->response->body, true),
        ], $examples);
    }

    protected static function transformVariables(array $variables, array $options = []): array
    {
        return array_map(function (Variable $variable) {
            return [
                'key' => $variable->name,
                'value' => $variable->value,
                'type' => self::postmanType($variable->type ?? gettype($variable->value)),
                'description' => $variable->description,
            ];
        }, $variables);
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