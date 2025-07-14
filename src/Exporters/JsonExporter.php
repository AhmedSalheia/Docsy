<?php

namespace Ahmedsalheia\Docsy\Exporters;

use Ahmedsalheia\Docsy\DocsyCollection;

class JsonExporter extends AbstractExporter
{
    public static function export(DocsyCollection $collection): array
    {
        return $collection->toArray();
    }
}