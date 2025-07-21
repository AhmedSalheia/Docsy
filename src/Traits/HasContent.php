<?php

namespace Docsy\Traits;

use Docsy\Docsy;
use Docsy\Folder;
use Docsy\Request;

trait HasContent
{
    private array $content = [];
    private array $requests = [];
    private array $folders = [];

    public function add(Request|Folder $data): static
    {
        $data->setParent($this);
        $this->content[$data->id] = $data;

        return $this;
    }

    public function get($name_or_id_or_chain): Folder|Request|array|null
    {
        if ($this->hasID($name_or_id_or_chain))
            return $this->getByID($name_or_id_or_chain);

        elseif ($this->hasName($name_or_id_or_chain))
            return $this->getByName($name_or_id_or_chain);

        else
            return $this->getByChain($name_or_id_or_chain);
    }

    private function getByChain(string $chain): Folder|Request|array|null
    {
        // by id/name chaining:
        $chain = explode('.', $chain);
        $object = $this;

        // collection.folder.folder.request

        if (!empty($chain)) {
            foreach ($chain as $child_id_or_name) {

                if (!$object) return null;

                if (is_a($object, Docsy::class)) // if docsy class getCollection, others don't need it
                    if ($object->hasCollection($child_id_or_name)) {
                        $object = $object->collection($child_id_or_name);
                        continue;
                    } else
                        return null;

                if (is_array($object)) // array of collections or folders
                    $object = [...array_map(
                        fn($item) => $item->get($child_id_or_name),
                        array_values((array)array_filter($object, fn($item) => $item->has($child_id_or_name)))
                    )];

                else // folder or collection
                    $object = $object->get($child_id_or_name);

                if ($child_id_or_name === $chain[array_key_last($chain)]) // end reached
                    return $object;
            }
        }

        return null;
    }

    private function getByID(string $id): Folder|Request
    {
        return $this->content[$id];
    }

    private function getByName(string $name): Folder|Request|array
    {
        $items = array_filter($this->content, function (Request|Folder $item) use ($name) {
            return $item->name === $name;
        });
        return count($items) == 1 ? array_shift($items) : $items;
    }

    /**
     * @throws \Exception
     */
    public function flatten(string $class_name): array
    {
        if ($class_name === '')
            throw new \Exception("class_name can't be empty");

        elseif (!in_array($class_name, [Request::class, Folder::class]))
            throw new \Exception("only [" . Request::class . ", " . Folder::class . "] are currently supported");

        $arr = basename(strtolower($class_name)) . 's';

        foreach ($this->content as $item) {
            if ($item instanceof Request) $this->requests[$item->id] = $item;
            else if ($item instanceof Folder) {
                $this->folders[$item->id] = $item;
                $this->$arr = array_merge($this->$arr, $item->flatten($class_name));
            }
        }

        return $this->$arr;

    }

    public function remove(string $id): static
    {
        if ($this->has($id))
            unset($this->content[$id]);

        return $this;
    }

    public function has(string $id_or_name): bool
    {
        return $this->hasID($id_or_name) || $this->hasName($id_or_name);
    }

    private function hasID(string $id): bool
    {
        return array_key_exists($id, $this->content);
    }

    private function hasName(string $name): bool
    {
        $items = $this->getByName($name);
        return count($items) > 0;
    }

    public function count(string $class_name): int
    {
        return count($this->flatten($class_name));
    }

    public function content(): array
    {
        return $this->content;
    }

    public function filter(callable $callback): array
    {
        return array_filter($this->content, $callback);
    }
}