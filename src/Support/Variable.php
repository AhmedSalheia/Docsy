<?php

namespace Docsy\Support;

use Docsy\Traits\ArrayJsonSerialization;
use Docsy\Traits\CouldBeDisabled;
use Docsy\Traits\HasID;
use Docsy\Traits\HasMeta;
use Docsy\Traits\HasParent;

class Variable implements \JsonSerializable
{
    use ArrayJsonSerialization, HasParent, HasID, CouldBeDisabled, HasMeta;

    public string $name;
    public string $value;
    public string $type;
    public string $description = '';

    public function __construct(string $name, string $value, string $type = null, string $description = '')
    {
        $this->setID();
        $this->name = $name;
        $this->value = $value;
        $this->type = $type ?? gettype($value);
        $this->description = $description;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'class_name' => basename(get_class($this)),
            'meta' => $this->meta,
            'name' => $this->name,
            'value' => $this->value,
            'type' => $this->type,
            'description' => $this->description,
            'disabled' => $this->disabled
        ];
    }

    public static function fromArray(array $array, $parent = null): static
    {
        return (new static(
            $array['name'],
            $array['value'],
            $array['type']??null,
            $array['description']??'')
        )
            ->setParent($parent)
            ->setID($array['id']??null)
            ->is_disabled($array['disabled']??false)
            ->setMeta($array['meta']??null);
    }
}