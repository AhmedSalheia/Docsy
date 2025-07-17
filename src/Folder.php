<?php

namespace Docsy;

use Docsy\Traits\ArrayJsonSerialization;
use Docsy\Traits\HasContent;
use Docsy\Traits\HasID;
use Docsy\Traits\HasParent;

class Folder implements \JsonSerializable
{
    use HasParent, ArrayJsonSerialization, HasID, HasContent;

    public string $name;
    public string $description;
    public bool $requires_auth = false;

    public function __construct(string $name, string $description = '', bool $requires_auth = false)
    {
        $this->setID();
        $this->name = $name;
        $this->description = $description;
        $this->requires_auth = $requires_auth;
    }
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'class_name' => basename(get_class($this)),
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
            $class = $content['class_name'] === 'Folder' ? Folder::fromArray($content) : Request::fromArray($content);
            $class->setParent($folder);
        }
        return $folder->setParent($parent)->setID($array['id']??null);
    }
}
