<?php

namespace Docsy\Utility\interfaces;

use Docsy\Docsy;

interface Exporter
{
    static function export(Docsy $docsy, string $collection = "", array $options = []): string;
    static function file_ext(): string;
}