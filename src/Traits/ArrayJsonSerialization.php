<?php

namespace Docsy\Traits;

use Docsy\Collection;
use Docsy\Folder;

trait ArrayJsonSerialization
{
    abstract public function toArray(): array;
    abstract public static function fromArray(array $array, $parent = null) : static;
    public static function fromArrayCollection($parent, array ...$objects) : array
    {
        $return = [];
        foreach ($objects as $object) {
            if (is_a($object,static::class)) $return[$object->id] = $object;
            else {
                $object = static::fromArray($object, $parent)->setID($object['id'] ?? null);
                $return[$object->id] = $object;
            }
        }
        return $return;
    }
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

}