<?php

namespace Docsy\Utility\Generators;

use Docsy\Collection;
use Docsy\Docsy;
use Docsy\Folder;
use Docsy\Request;
use Docsy\Utility\Param;
use Docsy\Utility\Variable;

class MarkdownGenerator extends AbstractGenerator
{
    protected static string $generated_file_ext = 'md';

    public static function generate(Docsy $docsy, string $collection = "", array $options = []): string
    {
        $collections = array_values(array_map(fn ($collection) => self::transformCollection($collection, $options), $docsy->collections()));

        return "# API Documentation\n\n" . implode("\n___\n", $collections);
    }

    protected static function transformCollection(Collection $collection, array $options = []): string
    {
        $output = "## $collection->name:\n\n";

        $output .= "- **Version**: $collection->version.\n";
        $output .= "- **Description**: $collection->description.\n";
        $output .= "- **Base Url**: $collection->baseUrl\n";

        if(!empty($collection->getVariables()))
            $output .= "- **Variables**: \n\t" . implode("\n\t", array_map(fn ($variable) => self::transformVariables($variable, $options), $collection->getVariables()));
        else
            $output .= "- **Variables**: \n";

        $content = array_map(fn ($item) => self::transformContent($item,options: $options), $collection->content());

        return $output . "\n" . implode("\n\n", $content);
    }
    protected static function transformVariables(Variable $variable, array $options = []): string
    {
        $output = "- **$variable->name**:\n\t\t";
        $output .= "- **Description**: $variable->description\n\t\t";
        $output .= "- **Type**: $variable->type\n\t\t";
        $output .= "- **Value**: $variable->value\n";

        return $output;
    }

    protected static function transformFolder(Folder $folder, int $level = 1, array $options = []): string
    {
        $output = "##" . str_repeat("#", $level) . " {$folder->name}:\n\n";
        $output .= "- **Description**: {$folder->description}\n";
        $output .= "- **Requires Auth**: ". ($folder->requires_auth?'True':'False') . "\n";

        $content = array_map(fn ($item) => self::transformContent($item,$level + 1, $options), $folder->content());

        return $output . "\n" . implode("\n\n", $content);
    }

    protected static function transformRequest(Request $request, int $level = 1, array $options = []): string
    {
        $level = $level === 1? "###": "";
        $output =  $level . " _**{$request->name}**_: \n\n**{$request->method->value}**`{$request->uri}`\n\n";
        $output .= "- **Description**: {$request->description}\n\n";

        $output .= self::listParams('Path Params', $request->pathParams);
        $output .= self::listParams('Query Params', $request->queryParams);
        $output .= self::listParams('Header Params', $request->headerParams);
        $output .= self::listParams('Body Params', $request->bodyParams);

        $output .= "- **Requires Auth**: ". ($request->requires_auth?'True':'False') . "\n";
        if ($request->is_auth)
            $output .= "- **This is the Auth Request.**";

        return $output;
    }

    protected static function transformParam(Param $param, array $options = []): string
    {
        $output = "- **{$param->name}**:\n\t\t";
        $output .= "- **Description**: {$param->description}\n\t\t";
        $output .= "- **Type**: {$param->type}\n\t\t";
        $output .= "- **Value**: {$param->value}\n\t\t";
        $output .= "- **Required**: ". ($param->required ? "True": "False") ."\n";

        return $output;
    }

    protected static function listParams(string $name, array $param_container, $options = []): string
    {
        if (!empty($param_container))
            $output = "- **$name**: \n\t" . implode("\n\t", array_map(fn ($param) => self::transformParam($param, $options), $param_container)) . "\n";
        else
            $output = "- **$name**: \n\n";

        return $output;
    }
}