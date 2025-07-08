<?php

namespace Ahmedsalheia\Docsy;

use Ahmedsalheia\Docsy\Enums\ParamLocation;
use PhpParser\Comment\Doc;

class Docsy
{
    private static array $collections = [];

    public function __construct() {
        $this->addCollection();
    }

    /**
     * @returns Docsy
     */
    public function addCollection(?string $name = null, string $description = '', string $version = "1.0.0"): static
    {
        if ($name == null) {
            $name = config('docsy.default_collection.name');
            $description = config('docsy.default_collection.description');
            $version = config('docsy.default_collection.version');
        }

        if (!in_array($name, array_keys(self::$collections))) {
            self::$collections[$name] = new DocsyCollection($name, $description, $version);
        }

        return $this;
    }

    /**
     * @returns DocsyCollection | null
     */
    public function collection(?string $name = null) : ?DocsyCollection
    {
        if ($name == null) $name = config('docsy.default_collection.name');
        if (in_array($name, array_keys(self::$collections))) {
            return self::$collections[$name];
        }
        return null;
    }

    /**
     * @returns DocsyCollection[]
     */

    public function collections(): array
    {
        return self::$collections;
    }
}
