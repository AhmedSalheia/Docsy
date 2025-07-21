<?php

namespace Docsy\Utility\Exporters;

use Docsy\Collection;
use Docsy\Folder;
use Docsy\Request;
use Docsy\Utility\interfaces\Exporter;

abstract class AbstractExporter implements Exporter
{
    protected static string $export_file_ext = 'json';

    public static function file_ext(): string
    {
        return self::$export_file_ext;
    }

    abstract protected static function transformCollection(Collection $collection, array $options = []): array;
    protected static function transformContent(array $content, array $options = []): array
    {
        return array_map(function ($item) use ($options) {

            if ($item instanceof Request) {
                return static::transformRequest($item, $options);
            }

            if ($item instanceof Folder) {
                return static::transformFolder($item, $options);
            }

            return [];
        }, $content);
    }
    abstract protected static function transformFolder(Folder $folder, array $options = []): array;

    abstract protected static function transformRequest(Request $request, array $options = []): array;

    abstract protected static function transformRequestUrl(string $base_url, array $path, array $queryParams = [], array $pathParams = [], array $options = []): array | string;
    abstract protected static function transformRequestHeaders(array $headers, array $options): array;
    abstract protected static function transformRequestBody(array $body, array $options = []): array;
    abstract protected static function transformRequestAuth(bool $auth, array $options = []): array;
    abstract protected static function transformRequestExamples(array $examples, array $options = []): array;
    abstract protected static function transformVariables(array $variables, array $options = []): array;
}