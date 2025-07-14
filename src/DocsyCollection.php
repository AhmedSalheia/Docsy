<?php

namespace Ahmedsalheia\Docsy;

use Ahmedsalheia\Docsy\traits\ArrayJsonSerialization;
use Ahmedsalheia\Docsy\traits\HasGlobals;
use Symfony\Component\Yaml\Yaml;

class DocsyCollection implements \JsonSerializable
{
    use HasGlobals, ArrayJsonSerialization;

    public string $name;
    public string $description;
    public string $version = "1.0.0";
    public string $baseUrl;
    private array $content = [];
    protected array $variables = [];

    public function __construct(string $name, string $description, string $version = "1.0.0",?string $baseUrl = null)
    {
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
        $this->addVariable('base_url', [
            'value' => $baseUrl,
            'type' => 'string',
            'description' => 'Base URL of the API'
        ]);
        return $this;
    }

    /** Content */
    public function add(DocsyRequest | DocsyFolder $data): static
    {
        $data->setParent($this);
        $this->content[$data->name . (is_a($data, DocsyRequest::class)? 'Request' : 'Folder')] = $data;

        return $this;
    }
    public function remove(string $name) : static
    {
        if($this->has($name))
            unset($this->content[$name]);

        return $this;
    }
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->content);
    }
    public function content() : array
    {
        return $this->content;
    }

    /** Variables */
    public function addVariable(string $name, mixed $value): static
    {
        $this->variables[$name] = $value;
        return $this;
    }
    public function addVariables(array $variables): static
    {
        foreach ($variables as $name => $value) {
            $this->addVariable($name, $value);
        }

        return $this;
    }
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * exporting:
     *
     * to Postman:
     */

    public function export($formatter, $options = [], ?string $save_path = null) : void
    {
        $formatter = explode('.',$formatter);
        $exporter_name = ucfirst(strtolower($formatter[0]));
        $format = $formatter[1] ?? 'json';

        $exporter_class = 'Ahmedsalheia\\Docsy\\Exporters\\' . $exporter_name .'Exporter';
        if (!class_exists($exporter_class)) throw new \InvalidArgumentException("$exporter_name is not supported as an exporter");

        $exporter = new $exporter_class();

        if ($save_path == null) $save_path = config('docsy.export_path');

        if ($format == 'json')
            $data = json_encode($exporter::export($this), ($options['pretty'] ?? true) ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0);
        elseif (in_array($format, ['yml', 'yaml']))
            $data = Yaml::dump($exporter::export($this), 10, $options['indent'] ?? 2,Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);
        else
            throw new \InvalidArgumentException("$exporter_name is not supported as exporter");

        file_put_contents(rtrim($save_path,'/') . '/' . $this->name . '_' . $exporter_name . '.' . $format , $data);
    }

    public function toArray(): array
    {
        return [
            'class_name' => 'DocsyCollection',
            'name' => $this->name,
            'description' => $this->description,
            'version' => $this->version,
            'variables' => $this->getVariables(),
            'globals' => array_map(function ($group) {
                return array_map(fn($param) => $param instanceof \JsonSerializable ? $param->jsonSerialize() : $param, $group);
            }, $this->globals()),
            'content' => array_map(fn($item) => $item instanceof \JsonSerializable ? $item->jsonSerialize() : $item, $this->content()),
        ];
    }
    public static function fromArray(array $array, $parent = null): static
    {
        $collection = new static(name: $array['name']??'', description: $array['description']??'', version: $array['version']??'', baseUrl: $array['base_url']??'');
        foreach ($array['variables'] ?? [] as $name => $value) {
            $collection->addVariable($name, $value);
        }
        $globals = [
            'headers' => DocsyParam::fromArrayCollection($collection, ...$array['globals']['headers'] ?? []),
            'body' => DocsyParam::fromArrayCollection($collection, ...$array['globals']['body'] ?? []),
            'query' => DocsyParam::fromArrayCollection($collection, ...$array['globals']['query'] ?? []),
        ];
        $collection->setGlobals($globals);
        foreach ($array['content'] as $content) {
            $class = $content['class_name'] === 'DocsyFolder' ? DocsyFolder::fromArray($content) : DocsyRequest::fromArray($content);
            $class->setParent($collection);
        }
        return $collection;
    }
}
