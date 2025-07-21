<?php

namespace Docsy\Utility\Exporters\openAPI;

use Docsy\Docsy;
use Symfony\Component\Yaml\Yaml;

class YamlExporter extends OpenApiAbstractExporter
{
    public static string $export_file_ext = 'yaml';
    public static function export(Docsy $docsy, string $collection = "", array $options = []): string
    {
        if ($collection == "")
            throw new \InvalidArgumentException('OpenAPI YamlExporter only meant to export single collection at a time');

        return Yaml::dump($docsy->getCollection($collection),10, 2,Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);
    }
}