<?php

namespace Docsy\Utility\Generators;

use Docsy\Collection;
use Docsy\Utility\interfaces\Generator;

abstract class AbstractGenerator implements Generator
{
    abstract protected function transformCollection(Collection $collection, array $options = []) : string | array;
}