<?php

namespace Docsy\Utility\interfaces;

use Docsy\Docsy;

interface Generator
{
    public static function generate(Docsy $docsy, string $collection = "", array $options = []): string;
}