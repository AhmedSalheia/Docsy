<?php

namespace Docsy\Utility\Exporters;

use Docsy\Collection;
use Docsy\Docsy;
use Docsy\Folder;
use Docsy\Request;

class JsonExporter extends AbstractExporter
{
    public static string $export_file_ext = 'json';

    public static function export(Docsy $docsy, string $collection = "", array $options = []): string
    {
       return json_encode($collection === '' ? $docsy : $docsy->getCollection($collection),JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected static function transformCollection(Collection $collection): array
    {
        return [];
    }

    protected static function transformVariables(array $variables, array $options = []): array
    {
        return [];
    }

    protected static function transformFolder(Folder $folder, array $options = []): array
    {
        return [];
    }

    protected static function transformRequest(Request $request, array $options = []): array
    {
        return [];
    }

    protected static function transformRequestUrl(string $base_url, array $path, array $queryParams = [], array $pathParams = [], array $options = []): array | string
    {
        return [];
    }

    protected static function transformRequestBody(array $body, array $options = []): array
    {
        return [];
    }

    protected static function transformRequestAuth(bool $auth, array $options = []): array
    {
        return [];
    }

    protected static function transformRequestExamples(array $examples, array $options = []): array
    {
        return [];
    }

    protected static function transformRequestHeaders(array $headers, array $options): array
    {
        return [];
    }
}