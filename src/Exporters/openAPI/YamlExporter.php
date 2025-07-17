<?php

namespace Docsy\Exporters\openAPI;

use Docsy\Docsy;
use Docsy\Exporters\OpenapiExporter;
use Symfony\Component\Yaml\Yaml;

class YamlExporter extends openapiExporter
{
    public static string $export_file_ext = 'yaml';
    public static function export(Docsy $docsy, $collection = ""): string
    {
        if ($collection == "")
            throw new \InvalidArgumentException('OpenAPI YamlExporter only meant to export single collection at a time');

        return Yaml::dump($docsy->getCollection($collection),10, 2,Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);
    }
}