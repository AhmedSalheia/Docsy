<?php

namespace Ahmedsalheia\Docsy;

use Ahmedsalheia\Docsy\Enums\ParamLocation;
use Ahmedsalheia\Docsy\traits\ArrayJsonSerialization;
use Ahmedsalheia\Docsy\traits\HasGlobals;
use Ahmedsalheia\Docsy\traits\HasParent;

class DocsyFolder implements \JsonSerializable
{
    use HasParent, ArrayJsonSerialization;

    public string $name;
    public string $description;
    public bool $requires_auth = false;
    public array $content = [];

    public function __construct(string $name, string $description = '', bool $requires_auth = false)
    {
        $this->name = $name;
        $this->description = $description;
        $this->requires_auth = $requires_auth;
    }

    public function getCollection(): ?DocsyCollection
    {
        $parent = $this->getParent();

        while ($parent !== null) {
            if ($parent instanceof DocsyCollection) {
                return $parent;
            }
            $parent = $parent->getParent();
        }

        return null;
    }

    /** Content */
    public function add(DocsyRequest | DocsyFolder $data): static
    {
        $data->setParent($this);
        $data->requires_auth = $this->requires_auth;

        $this->content[$data->name] = $data;

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

    public function toArray(): array
    {
        return [
            'class_name' => 'DocsyFolder',
            'name' => $this->name,
            'description' => $this->description,
            'requires_auth' => $this->requires_auth,
            'content' => array_map(fn($item) => $item instanceof \JsonSerializable ? $item->jsonSerialize() : $item, $this->content())
        ];
    }
    public static function fromArray(array $array, $parent = null) : static
    {
        $folder = new static($array['name'], $array['description']??'', $array['requires_auth']??false);

        foreach ($array['content'] as $content) {
            $class = $content['class_name'] === 'DocsyFolder' ? DocsyFolder::fromArray($content) : DocsyRequest::fromArray($content);
            $class->setParent($folder);
        }
        return $folder->setParent($parent);
    }
}
