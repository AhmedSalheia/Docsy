<?php

namespace Docsy\Utility\Importers;

use Docsy\Collection;
use Docsy\Folder;
use Docsy\Request;
use Docsy\Utility\Example;
use Docsy\Utility\interfaces\Importer;
use Docsy\Utility\Param;
use Docsy\Utility\Variable;

abstract class AbstractImporter implements Importer
{
    abstract protected static function transformCollection(array $data, array $options = []): Collection;
    abstract protected static function transformVariable(array $variable, array $options = []) : Variable;
    protected static function transformContent(array $item, array $options = []): Request|Folder
    {
        $is_folder = isset($item['item']) && is_array($item['item']);

        if (!$is_folder) {
            return static::transformRequest($item, $options);
        }

        return static::transformFolder($item, $options);
    }

    abstract protected static function transformFolder(array $folder, array $options = []): Folder;

    abstract protected static function transformRequest(array $request, array $options = []): Request;
    abstract protected static function transformRequestUrl(array $url, array $options = []): string;
    abstract protected static function transformRequestPath(array $pathParams, array $options = []): array | null;
    abstract protected static function transformRequestQuery(array $queryParams, array $options = []): array;
    abstract protected static function transformRequestHeaders(array $headerParams, array $options = []): array;
    abstract protected static function transformRequestBody(array $body, array $options = []): array;
    abstract protected static function transformRequestAuth(array $auth, array $options = []): bool;
    abstract protected static function transformRequestExamples(array $example, array $options = []): Example;
}