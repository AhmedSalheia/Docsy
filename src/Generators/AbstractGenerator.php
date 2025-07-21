<?php

namespace Docsy\Generators;

use Docsy\Docsy;

abstract class AbstractGenerator
{
    abstract public static function generate(Docsy $docsy, array $options = []): string;
}