<?php

namespace Docsy\Traits;

use Docsy\Docsy;
use Docsy\Folder;
use Docsy\Request;

trait HasContent
{
    private array $content = [];

    public function add(Request | Folder $data): static
    {
        $data->setParent($this);
        $this->content[$data->id] = $data;

        return $this;
    }

    public function get($name_or_id_or_chain) : Folder | Request | array | null
    {
        if ($this->hasID($name_or_id_or_chain))
            return $this->content[$name_or_id_or_chain];
        elseif ($this->hasName($name_or_id_or_chain))
            return $this->getByName($name_or_id_or_chain);
        else
            return $this->getByChain($name_or_id_or_chain);
    }
    private function getByChain(string $chain) : Folder | Request | array | null
    {
        // by id/name chaining:
        $chain = explode('.', $chain);
        $object = $this;

        // collection.folder.folder.request

        if (!empty($chain))
        {
            foreach ($chain as $child_id_or_name) {

                if (is_a($object, Docsy::class)) // if docsy class getCollection, others don't need it
                    if ($object->hasCollection($child_id_or_name)) {
                        $object = $object->getCollection($child_id_or_name);
                        continue;
                    } else
                        return null;

                if (is_array($object)) // array of collections or folders
                    $object = [...array_map(
                        fn ($item) => $item->get($child_id_or_name),
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
    private function getByID(string $id) : Folder | Request
    {
        return $this->content[$id];
    }
    private function getByName(string $name) : Folder | Request | array
    {
        $items = array_filter($this->content, function (Request|Folder $item) use ($name) {
            return $item->name === $name;
        });
        return count($items) == 1? array_shift($items): $items;
    }

    public function remove(string $id) : static
    {
        if($this->has($id))
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
        $items = array_filter($this->content, function (Request|Folder $item) use ($name) {
            return $item->name === $name;
        });
        return count($items) > 0;
    }
    public function count(string $class_name) : int
    {
        $count = 0;

        foreach ($this->content as $item)
        {
            if (is_a($item, $class_name)) $count++;

            if(method_exists($item,'count')) $count += $item->count($class_name);
        }

        return $count;
    }
    public function content() : array
    {
        return $this->content;
    }
}