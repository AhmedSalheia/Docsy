<?php

namespace Ahmedsalheia\Docsy;

use Ahmedsalheia\Docsy\traits\ArrayJsonSerialization;

class Docsy implements \JsonSerializable
{
    /** @var DocsyCollection[] $collections */
    private array $collections = [];
    private static ?Docsy $instance;

    public function __construct() {
        $this->addCollection();
    }

    /**
     * @param bool $force_new
     * @return Docsy
     */
    public static function getInstance(bool $force_new = false): static
    {
        if ($force_new) return new static();
        self::$instance = self::$instance ?? new static();
        return self::$instance;
    }

    /**
     * @param self $instance
     * @return Docsy
     */
    public static function setInstance(self $instance): Docsy
    {
        self::$instance = $instance;
        return self::$instance;
    }

    /**
     * @returns Docsy
     */
    public function addCollection(string | DocsyCollection $collection = null, string $description = '', string $version = "1.0.0"): static
    {
        $name = is_a($collection, DocsyCollection::class) ? $collection->name : $collection;

        if ($collection == null) {
            $name = config('docsy.default_collection.name');
            $description = config('docsy.default_collection.description');
            $version = config('docsy.default_collection.version');
        }

        if (!array_key_exists($name, $this->collections)) {
            $this->collections[$name] = is_a($collection, DocsyCollection::class) ? $collection->name : new DocsyCollection($name, $description, $version);
        }

        return $this;
    }

    /**
     * @param string $name the name of the collection to remove
     * @return Docsy
     * **/
    public function removeCollection(string $name) : static
    {
        if($this->hasCollection($name))
            unset($this->collections[$name]);

        return $this;
    }
    /**
     * @returns DocsyCollection | null
     */
    public function collection(?string $name = null) : ?DocsyCollection
    {
        if ($name == null) $name = config('docsy.default_collection.name');
        if (array_key_exists($name, $this->collections)) {
            return $this->collections[$name];
        }
        return null;
    }
    /**
     * @param string $name
     * @return bool
     */
    public function hasCollection(string $name): bool
    {
        return array_key_exists($name, $this->collections);
    }

    /**
     * @returns DocsyCollection[]
     */
    public function collections(): array
    {
        return $this->collections;
    }
    /**
     * @returns DocsyCollection[]
     */
    public function collectionNames(): array
    {
        return array_keys($this->collections);
    }

    /**
     * Export All Collection a file, or multiple files
     *
     * @param string $formatter The Export Format, Currently Supported ["postman", "openapi.json", "openapi.yaml" and "json"]
     * @param bool $single_file Optional, if the format is set to "json" save the whole docsy to a single file or to multiple files @Default: false
     * @param string|null $save_path The path to save files to
     * @return array exported paths
     *
     */
    public function export(string $formatter, bool $single_file = false, ?string $save_path = null) : void
    {
        $formatter = explode('.',$formatter);
        $format = $formatter[1] ?? 'json';

        $paths = [];
        if ($save_path == null) $save_path = rtrim(config('docsy.export_path'),'/');

        if ($format !== 'json' || !$single_file) {
            foreach ($this->collections() as $collection) {
                $collection->export($formatter);
            }
        }

        $single_save_path = $save_path . '/Docsy_'. date('Y_m_d_h_i_s') .'.json';
        file_put_contents($single_save_path, json_encode($this, JSON_PRETTY_PRINT));
    }

    public function reset(): static
    {
        $this->collections = [];
        self::$instance = null;
        return $this;
    }
    public function toArray(): array
    {
        return [
            'class_name' => 'Docsy',
            'collections' => $this->collections(),
        ];
    }

    public static function fromArray(array $array, $force_new = false): static
    {
        $docsy = static::getInstance($force_new);
        $docsy->collections = DocsyCollection::fromArrayCollection(null, ...$array['collections']);

        return $docsy;
    }
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
