<?php

namespace Ahmedsalheia\Docsy;

use Ahmedsalheia\Docsy\Exporters\OpenApiExporter;
use Ahmedsalheia\Docsy\Exporters\PostmanExporter;
use Ahmedsalheia\Docsy\traits\HasGlobals;
use Symfony\Component\Yaml\Yaml;

class DocsyCollection
{
    use HasGlobals;

    public string $name;
    public string $description;
    public string $version = "1.0.0";
    public array $content = [];
    protected array $variables = [];

    public function __construct(string $name, string $description, string $version = "1.0.0")
    {
        $this->name = $name;
        $this->description = $description;
        $this->version = $version;
    }

    public function setVersion(string $version = "1.0.0") : static
    {
        $this->version = $version;
        return $this;
    }

    public function add(DocsyRequest | DocsyFolder $data): static
    {
        $data->setParent($this);
        $this->content[] = $data;
        $data->setGlobals($this->globals());

        return $this;
    }

    public function addVariable(string $name, mixed $value): static
    {
        $this->variables[$name] = $value;
        return $this;
    }
    /** Variables */
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
    public function toPostmanJson(bool $pretty = true): string
    {
        $data = PostmanExporter::export($this);
        return json_encode($data, $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0);
    }

    public function savePostmanAs(string $filePath, bool $pretty = true): bool
    {
        $json = $this->toPostmanJson($pretty);
        return file_put_contents($filePath, $json) !== false;
    }

    /**
     *  to OpenAPI: Json and Yaml
     * */
    public function toOpenApiJson(bool $pretty = true): string
    {
        $data = OpenApiExporter::export($this);
        return json_encode($data, $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0);
    }
    public function toOpenApiYaml(int $indent = 2): string
    {
        $data = OpenApiExporter::export($this);
        return Yaml::dump($data, 10, $indent, Yaml::DUMP_OBJECT_AS_MAP);
    }
    /**
     * @throws \Exception
     */
    public function saveOpenApiAs(string $filePath, bool | int $extra = null): bool
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $data = in_array($ext, ['yml', 'yaml'])
            ? $this->toOpenApiYaml($extra ?? 2)
            : $this->toOpenApiJson($extra ?? true);

        return file_put_contents($filePath, $data) !== false;
    }
}
