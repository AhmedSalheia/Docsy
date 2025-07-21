<?php

namespace Docsy\Utility\Generators;

use Docsy\Collection;
use Docsy\Docsy;

class MarkdownGenerator extends AbstractGenerator
{
    public static function generate(Docsy $docsy, array $options = []): string
    {
        $output = "# API Documentation\n\n";
        foreach ($docsy->collections() as $collection) {
            $output .= "## {$collection->name}\n\n";
            foreach ($collection->requests() as $request) {
                $output .= "### {$request->method->value} {$request->path}\n";
                $output .= "{$request->description}\n\n";
                // add parameters, body, responses, etc.
            }
        }
        return $output;
    }

    protected function transformCollection(Collection $collection, array $options = []): string|array
    {
        return [];
    }
}