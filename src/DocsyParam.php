<?php

namespace Ahmedsalheia\Docsy;

use Ahmedsalheia\Docsy\Enums\ParamLocation;
use Ahmedsalheia\Docsy\traits\ArrayJsonSerialization;
use Ahmedsalheia\Docsy\traits\HasParent;

class DocsyParam implements \JsonSerializable
{
    use HasParent, ArrayJsonSerialization;

    public string $name;
    public ParamLocation $in;

    public string $description = '';
    public string $type = 'string';
    public bool $required = false;
    public mixed $example = null;

    public function __construct(string $name, ParamLocation $in, string $description = '', string $type = '', bool $required = false, mixed $example = null,?DocsyRequest $parent = null) {
        $this->name = $name;
        $this->in = $in;
        $this->description = $description;
        $this->type = $type ?: gettype($example ?? '');
        $this->required = $required;
        $this->example = $example;
        $this->setParent($parent);
    }

    public function setExample($example): static
    {
        $this->example = $example;
        $this->type = $this->type ?: gettype($example ?? '');
        return $this;
    }

    public function toArray(): array
    {
        return [
            'class_name' => 'DocsyParam',
            'name' => $this->name,
            'in' => $this->in->value,
            'description' => $this->description,
            'type' => $this->type,
            'required' => $this->required,
            'example' => $this->example
        ];
    }
    public static function fromArray(array $array, $parent = null): static
    {
        return (new self($array['name'], ParamLocation::get($array['in']),$array['description']??'',$array['type']??'',(bool)$array['required']??false,$array['example']??null))->setParent($parent);
    }
}