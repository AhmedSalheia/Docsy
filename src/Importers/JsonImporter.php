<?php

namespace Docsy\Importers;

use Docsy\Docsy;
use Docsy\Collection;
use Docsy\Importers\AbstractImporter;

class JsonImporter extends AbstractImporter
{
    public static function import(bool $is_docsy_import, string ...$files): Docsy
    {
        if ($is_docsy_import)
            $docsy = Docsy::fromArray(json_decode(file_get_contents($files[0]),true));
        else {
            $docsy = Docsy::getInstance();
            foreach ($files as $file) {
                $collection = self::transformCollection(json_decode(file_get_contents($file), true));
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