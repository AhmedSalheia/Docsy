<?php

namespace Docsy\Importers;

use Docsy\Collection;
use Docsy\Docsy;
use Symfony\Component\Yaml\Yaml;

class YamlImporter extends AbstractImporter
{

    public static function import(bool $is_docsy_import, string ...$files): Docsy
    {
        if ($is_docsy_import)
            $docsy = Docsy::fromArray(Yaml::parseFile($files[0]));
        else {
            $docsy = Docsy::getInstance();
            foreach ($files as $file) {
                $collection = self::transformCollection(Yaml::parseFile($file));
                $docsy->addCollection($collection);
            }
        }

        return $docsy;
    }

    private static function transformCollection(array $data): Collection
    {
        return Collection::fromArray($data);
    }
}