<?php

namespace Ahmedsalheia\Docsy\traits;

use Ahmedsalheia\Docsy\DocsyCollection;
use Ahmedsalheia\Docsy\DocsyFolder;

trait ArrayJsonSerialization
{
    abstract public function toArray(): array;
    abstract public static function fromArray(array $array, $parent = null) : static;
    public static function fromArrayCollection($parent, array ...$objects) : array
    {
        $return = [];
        foreach ($objects as $object) {
            if (is_a($object,static::class)) $return[$object->name] = $object;
            else $return[$object['name']] = static::fromArray($object, $parent);

        }
        return $return;
    }
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

}