<?php

namespace Ahmedsalheia\Docsy\Importers;

use Ahmedsalheia\Docsy\DocsyCollection;

abstract class AbstractImporter
{
    abstract public static function import(DocsyCollection $collection);
}