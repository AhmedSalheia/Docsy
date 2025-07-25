<?php

namespace Docsy;

use Docsy\Enums\ParamLocation;
use Docsy\Exporters\AbstractExporter;
use Docsy\Support\Param;
use Docsy\Traits\ArrayJsonSerialization;
use Docsy\Traits\HasContent;
use Docsy\Traits\HasGlobals;
use Docsy\Traits\HasID;
use Docsy\Traits\HasVariables;
use Exception;
use JsonSerializable;

class Collection implements JsonSerializable
{
    use HasGlobals, ArrayJsonSerialization, HasID, HasContent, HasVariables;

    public string $name;
    public string $description;
    public string $version = "1.0.0";
    public string $baseUrl;

    public function __construct(string $name, string $description, string $version = "1.0.0",?string $baseUrl = null)
    {
        $this->setID();
        $this->name = $name;
        $this->description = $description;
        $this->version = $version;
        $this->setBaseUrl($baseUrl ?? config('docsy.base_url'));
    }

    public function setVersion(string $version = "1.0.0") : static
    {
        $this->version = $version;
        return $this;
    }

    public function setBaseUrl(string $baseUrl) : static
    {
        $this->baseUrl = $baseUrl;
        $this->addVariable([
            'name' => 'base_url',
            'value' => $baseUrl,
            'type' => 'string',
            'description' => 'Base URL of the API'
        ]);
        return $this;
    }

    /**
     * Export All Collection a file, or multiple files
     *
     * @param string $formatter The Export Format, Currently Supported ["postman", "openapi.json", "openapi.yaml" and "json"]
     * @param string|null $save_dir The dir to save files to
     * @return void
     * @throws Exception
     */
    public function export(string $formatter, ?string $save_dir = null) : void
    {
        $formatters = config('docsy.formatters.exporters');
        $formatter_class = $formatters[$formatter] ?? null;
        if ($formatter_class == null)
            throw new Exception("Formatter '$formatter' not found");

        /* @var AbstractExporter $exporter */
        $exporter = new $formatter_class();

        if ($save_dir == null) $save_dir = rtrim(config('docsy.export_path'),'/');

        if (!file_exists($save_dir))
            mkdir($save_dir);

        $save_path = $save_dir . '/' . $this->name . '.' . $formatter . '.' . $exporter::$export_file_ext;
        $data = $exporter::export(Docsy::getInstance(), $this->id);
        file_put_contents($save_path, $data);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'class_name' => basename(get_class($this)),
            'name' => $this->name,
            'description' => $this->description,
            'version' => $this->version,
            'variables' => array_map(fn($item) => $item instanceof JsonSerializable ? $item->jsonSerialize() : $item, $this->getVariables()),
            'globals' => array_map(function ($group) {
                return array_map(fn($param) => $param instanceof JsonSerializable ? $param->jsonSerialize() : $param, $group);
            }, $this->globals()),
            'content' => array_map(fn($item) => $item instanceof JsonSerializable ? $item->jsonSerialize() : $item, $this->content()),
        ];
    }
    public static function fromArray(array $array, $parent = null): static
    {
        $collection = new static(name: $array['name']??'', description: $array['description']??'', version: $array['version']??'', baseUrl: $array['base_url']??'');
        $collection->addVariables($array['variables'] ?? []);
        $globals = [
            'headers' => Param::fromArrayCollection($collection,ParamLocation::Header, ...$array['globals']['headers'] ?? []),
            'body' => Param::fromArrayCollection($collection,ParamLocation::Body, ...$array['globals']['body'] ?? []),
            'query' => Param::fromArrayCollection($collection,ParamLocation::Query, ...$array['globals']['query'] ?? []),
        ];
        $collection->setGlobals($globals);
        $collection->setID($array['id']??null);
        foreach ($array['content'] as $content) {
            $class = $content['class_name'] === 'Folder' ? Folder::fromArray($content) : Request::fromArray($content);
            $class->setParent($collection);
        }
        return $collection;
    }
}
