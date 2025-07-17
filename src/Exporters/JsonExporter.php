<?php

namespace Docsy\Exporters;

use Docsy\Docsy;
use Docsy\Collection;
use Docsy\Folder;
use Docsy\Request;

class JsonExporter extends AbstractExporter
{
    public static string $export_file_ext = 'json';
    public static function export(Docsy $docsy, $collection = ""): string
    {
       return json_encode($collection === '' ? $docsy : $docsy->getCollection($collection),JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected static function transformCollection(Collection $collection): array
    {
        return [];
    }

    protected static function transformVariables(array $variables): array
    {
        return [];
    }

    protected static function transformFolder(Folder $folder): array
    {
        return [];
    }

    protected static function transformRequest(Request $request): array
    {
        return [];
    }

    protected static function transformRequestUrl(string $base_url, array $path, array $queryParams = [], array $pathParams = []): array | string
    {
        return [];
    }

    protected static function transformRequestBody(array $body): array
    {
        return [];
    }

    protected static function transformRequestAuth(bool $auth): array
    {
        return [];
    }

    protected static function transformRequestExamples(array $examples): array
    {
        return [];
    }
}