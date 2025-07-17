<?php

namespace Docsy\Traits;

use Docsy\Docsy;
use Docsy\Collection;

trait HasCollections
{
    /** @var Collection[] $collections */
    private array $collections = [];

    public function addCollection(string | Collection $collection = null, string $description = '', string $version = "1.0.0"): static
    {
        if ($collection == null) {
            $collection = config('docsy.default_collection.name');
            $description = config('docsy.default_collection.description');
            $version = config('docsy.default_collection.version');
        }
        $collection = is_a($collection, Collection::class) ? $collection : new Collection($collection, $description, $version);

        if (!$this->hasCollectionID($collection->id)) {
            $this->collections[$collection->id] = $collection;
        }

        return $this;
    }

    /**
     * @param string $name_or_id the name or id of the collection to remove
     * @return Docsy
     * **/
    public function removeCollection(string $name_or_id) : static
    {
        if($this->hasCollectionID($name_or_id))
            unset($this->collections[$name_or_id]);
        elseif ($this->hasCollectionName($name_or_id)) {
            $collections = $this->getCollection($name_or_id);
            if (is_array($collections))
                foreach ($collections as $collection) {
                    unset($this->collections[$collection->id]);
                }
            else unset($this->collections[$collections->id]);
        }
        return $this;
    }
    /**
     * @returns Collection | null
     */
    public function getCollection(?string $name_or_id = null) : Collection | array | null
    {
        if ($name_or_id == null) $name_or_id = config('docsy.default_collection.name');

        if ($this->hasCollectionID($name_or_id)) {

            return $this->collections[$name_or_id];

        } elseif ($this->hasCollectionName($name_or_id)) {
            $collections = array_filter($this->collections, function ($collection) use ($name_or_id) {
                return $collection->name == $name_or_id;
            });
            return count($collections) > 1 ? $collections : array_shift($collections);
        }
        return null;
    }

    public function hasCollection(string $name_or_id) : bool
    {
        return $this->hasCollectionID($name_or_id) || $this->hasCollectionName($name_or_id);
    }

    /**
     * @param string $id
     * @return bool
     */
    private function hasCollectionID(string $id): bool
    {
        return array_key_exists($id, $this->collections);
    }
    /**
     * @param string $name
     * @return bool
     */
    private function hasCollectionName(string $name): bool
    {
        return in_array($name, $this->collectionNames());
    }

    /**
     * @returns Collection[]
     */
    public function collections(): array
    {
        return $this->collections;
    }
    /**
     * @returns Collection[]
     */
    public function collectionNames(): array
    {
        return array_map(fn ($collection) => $collection->name, $this->collections);
    }
}