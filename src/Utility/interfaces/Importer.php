<?php

namespace Docsy\Utility\interfaces;

interface Importer
{
    static function import(array $options = [], string ...$files);
}