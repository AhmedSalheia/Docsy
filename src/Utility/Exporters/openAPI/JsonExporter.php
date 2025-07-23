<?php

namespace Docsy\Utility\Exporters\openAPI;

use Docsy\Docsy;

class JsonExporter extends OpenApiAbstractExporter
{
    public static function export(Docsy $docsy, string $collection = "", array $options = []): string
    {
        if ($collection == "")
            throw new \InvalidArgumentException('OpenAPI JsonExporter only meant to export single collection at a time');

        return json_encode(self::transformCollection($docsy->getCollection($collection)),JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}