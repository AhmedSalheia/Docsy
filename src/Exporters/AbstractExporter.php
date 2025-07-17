<?php

namespace Docsy\Exporters;

use Docsy\Docsy;
use Docsy\Collection;
use Docsy\Folder;
use Docsy\Request;

abstract class AbstractExporter
{
    public static string $export_file_ext = 'json';

    abstract public static function export(Docsy $docsy, $collection = ""): string;
    abstract protected static function transformCollection(Collection $collection): array;
    protected static function transformContent(array $content): array
    {
        return array_map(function ($item) {

            if ($item instanceof Request) {
                return static::transformRequest($item);
            }

            if ($item instanceof Folder) {
                return static::transformFolder($item);
            }

            return [];
        }, $content);
    }
    abstract protected static function transformFolder(Folder $folder): array;

    abstract protected static function transformRequest(Request $request): array;

    abstract protected static function transformRequestUrl(string $base_url, array $path, array $queryParams = [], array $pathParams = []): array | string;
    abstract protected static function transformRequestBody(array $body): array;
    abstract protected static function transformRequestAuth(bool $auth): array;
    abstract protected static function transformRequestExamples(array $examples): array;
    abstract protected static function transformVariables(array $variables): array;
}