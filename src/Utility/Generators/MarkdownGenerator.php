<?php

namespace Docsy\Utility\Generators;

use Docsy\Collection;
use Docsy\Docsy;
use Docsy\Folder;
use Docsy\Request;
use Docsy\Utility\Variable;

class MarkdownGenerator extends AbstractGenerator
{
    protected static string $generated_file_ext = 'md';

    public static function generate(Docsy $docsy, string $collection = "", array $options = []): string
    {
        $output = "# API Documentation\n\n";
        $collections = array_values(array_map(fn ($collection) => self::transformCollection($collection, $options), $docsy->collections()));

        foreach ($collections as $collection) {
            $output .= $collection;
        }

        return $output;
    }

    protected static function transformCollection(Collection $collection, array $options = []): string
    {
        $output = "## {$collection->name}:\n\n";

        $output .= "- Version: {$collection->version}.\n";
        $output .= "- Description: {$collection->description}.\n";
        $output .= "- Base Url: {$collection->baseUrl}\n";
        $output .= "- Variables: \n\t" . implode('\n\t', array_map(fn ($variable) => self::transformVariables($variable, $options), $collection->variables));

//        $content = array_map(fn ($item) => self::transformContent($item,options: $options), $collection->content());

        return $output;
    }
    protected static function transformVariables(Variable $variable, array $options = []): string
    {
        $output = "- {$variable->name}:\n\t\t";
        $output .= "- Description: {$variable->description}\n\t\t";
        $output .= "- Type: {$variable->type}\n\t\t";
        $output .= "- Value: {$variable->value}\n";

        return $output;
    }

    protected static function transformFolder(Folder $folder, int $level = 1, array $options = []): string
    {
        return "";
    }

    protected static function transformRequest(Request $request, int $level = 1, array $options = []): string
    {
        return "";
    }
}