<?php

namespace Docsy\Importers;

use Docsy\Docsy;
use Docsy\Collection;

abstract class AbstractImporter
{
    abstract public static function import(bool $is_docsy_import, string ...$files);
}