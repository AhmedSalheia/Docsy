<?php

namespace Docsy\Exporters\openAPI;

use Docsy\Docsy;
use Docsy\Exporters\OpenapiExporter;

class JsonExporter extends OpenapiExporter
{
    public static function export(Docsy $docsy, $collection = ""): string | false | array
    {
        if ($collection == "")
            throw new \InvalidArgumentException('OpenAPI JsonExporter only meant to export single collection at a time');

        return json_encode($docsy->getCollection($collection),JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}