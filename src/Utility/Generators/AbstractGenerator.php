<?php

namespace Docsy\Utility\Generators;

use Docsy\Collection;
use Docsy\Folder;
use Docsy\Request;
use Docsy\Utility\interfaces\Generator;
use Docsy\Utility\Variable;

abstract class AbstractGenerator implements Generator
{
    protected static string $generated_file_ext = 'txt';

    public static function file_ext(): string
    {
        return self::$generated_file_ext;
    }

    abstract protected static function transformCollection(Collection $collection, array $options = []) : string;
    protected static function transformContent(Folder|Request $item, int $level = 1, array $options = []): string
    {
        if ($item instanceof Request) {
            return static::transformRequest($item, $level, $options);
        }

        return static::transformFolder($item, $level, $options);
    }
    abstract protected static function transformVariables(Variable $variable, array $options = []): string;

    abstract protected static function transformFolder(Folder $folder, int $level = 1, array $options = []): string;

    abstract protected static function transformRequest(Request $request, int $level = 1, array $options = []): string;
}