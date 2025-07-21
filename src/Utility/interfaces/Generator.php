<?php

namespace Docsy\Utility\interfaces;

use Docsy\Docsy;

interface Generator
{
    public static function generate(Docsy $docsy, array $options = []): string;
}