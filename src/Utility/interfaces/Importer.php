<?php

namespace Docsy\Utility\interfaces;

use Docsy\Docsy;

interface Importer
{
    static function import(array $options = [], string ...$files) : Docsy;
}