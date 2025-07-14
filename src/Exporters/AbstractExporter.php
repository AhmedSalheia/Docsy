<?php

namespace Ahmedsalheia\Docsy\Exporters;

use Ahmedsalheia\Docsy\DocsyCollection;

abstract class AbstractExporter
{
    abstract public static function export(DocsyCollection $collection);
}