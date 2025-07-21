<?php

namespace Docsy\Utility\Importers;

use Docsy\Collection;
use Docsy\Docsy;

class JsonImporter extends AbstractImporter
{
    public static function import(array $options = [], string ...$files): Docsy
    {
        if ($options['single_file'])
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

    protected static function transformCollection(array|string $data, array $options = []): Collection
    {
        return Collection::fromArray($data);
    }
}