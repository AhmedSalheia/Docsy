<?php

namespace Docsy\Support;

use Docsy\Request;
use Docsy\Enums\ParamLocation;
use Docsy\Traits\ArrayJsonSerialization;
use Docsy\Traits\CouldBeDisabled;
use Docsy\Traits\HasID;
use Docsy\Traits\HasMeta;
use Docsy\Traits\HasParent;
use SebastianBergmann\Diff\InvalidArgumentException;

class Param implements \JsonSerializable
{
    use HasParent, ArrayJsonSerialization, HasID, CouldBeDisabled, HasMeta;

    public string $name;
    public ParamLocation $in;

    public string $description = '';
    public string $type = 'string';
    public bool $required = false;
    public mixed $value = null;
    private array $reservedNames = ['null','true','false'];

    public function __construct(string $name, ParamLocation $in, string $description = '', string $type = '', bool $required = false, mixed $value = null, ?Request $parent = null) {
        $this->setID();
        $this->in = $in;
        $this->name = $this->validateName($name);
        $this->description = $description;
        $this->type = $type ?: gettype($value ?? '');
        $this->required = $required;
        $this->value = $this->applyEncodingFunction($value ?? '');
        $this->setParent($parent);
    }
    private function applyEncodingFunction(string $string) : string
    {
        return $this->in == ParamLocation::Path ?
            rawurlencode($string) :
            (
                $this->in == ParamLocation::Query ?
                    urlencode($string) :
                    $string
            );
    }
    private function validateName(string $name): string
    {
        $name = trim($name);
        if ($name === "")
            throw new \InvalidArgumentException("Parameter name cannot be empty.");

        if (in_array(strtolower($name), $this->reservedNames))
            throw new InvalidArgumentException("Parameter name '$name' is reserved.");

        if (!preg_match('/^[a-zA-Z0-9_.-]+/', $name))
            throw new \InvalidArgumentException("Parameter name '$name' contains invalid characters.");

        return $this->applyEncodingFunction($name);
    }
    public function setValue($value): static
    {
        $this->value = $this->applyEncodingFunction($value);
        $this->type = $this->type ?: gettype($value ?? '');
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'class_name' => basename(get_class($this)),
            'meta' => $this->meta,
            'name' => $this->name,
            'in' => $this->in->value,
            'description' => $this->description,
            'type' => $this->type,
            'required' => $this->required,
            'value' => $this->value,
            'disabled' => $this->disabled
        ];
    }
    public static function fromArray(array $array, $parent = null): static
    {
        return (new self(
            $array['name'],
            ParamLocation::get($array['in']),
            $array['description']??'',
            $array['type']??'',
            (bool)$array['required']??false,
            $array['value']??null)
        )
            ->setParent($parent)
            ->setID($array['id']??null)
            ->is_disabled($array['disabled']??false)
            ->setMeta($array['meta'] ?? '');
    }

    public static function fromArrayCollection($parent, ParamLocation $in, ...$objects) : array
    {
        $return = [];
        foreach ($objects as $object) {

            if (is_a($object,self::class)) $object->in = $in;
            else {
                $object['in'] = $in;
                $object = self::fromArray($object, $parent);
            }

            $return[$object->id] = $object;
        }
        return $return;
    }
}