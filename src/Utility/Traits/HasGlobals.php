<?php

namespace Docsy\Utility\Traits;

use Docsy\Utility\Enums\ParamLocation;
use Docsy\Utility\Param;

trait HasGlobals
{
    /**
     * @var $globals array<string,array>
     */
    private array $globals = [
        'header' => [],
        'body' => [],
        'query' => []
    ];

    /**
     * @param  $globals array<string,array>
     */
    public function setGlobals(array $globals): static
    {
        $this->globals = $globals;
        if (!empty($this->content())) {
            foreach ($this->content() as $content) {
                $content->setGlobals($globals);
            }
        }
        return $this;
    }
    public function globals($key = null) : array
    {
        return $key? $this->globals[$key] : $this->globals;
    }

    public function addGlobal(string $location, string $name, mixed $value, string $description = '', bool $required = false)
    {
        if (!$this->hasGlobal($location,$name))
        {
            $param = new Param($name, ParamLocation::get($location), $description, required: $required, value: $value);
            $this->globals[$location][$param->id] = $param;
        } else
            $this->editGlobal($location, $name, $value, $description, $required);

        return $this;
    }
    public function addGlobalHeader(string $header, mixed $value, string $description = '', bool $required = false): static
    {
        $this->addGlobal('header', $header, $value, $description, $required);
        return $this;
    }
    public function addGlobalQueryParam(string $query, mixed $value, string $description = '', bool $required = false): static
    {
        $this->addGlobal('query', $query, $value, $description, $required);
        return $this;
    }
    public function addGlobalBodyParam(string $bodyParam, mixed $value, string $description = '', bool $required = false): static
    {
        $this->addGlobal('body', $bodyParam, $value, $description, $required);
        return $this;
    }

    public function editGlobal($location, $name_or_id, $value, string $description = '', bool $required = false): static
    {
        $header = $this->getGlobal($location, $name_or_id);
        $header->value = $value;
        $header->description = $description ?: $header->description;
        $header->required = $required ?: $header->required;
    }
    public function getGlobal($location, $name_or_id): param | null
    {
        $items = array_filter($this->globals[$location], fn ($item) => $name_or_id === $item->name);
        return $this->globals[$location][$name_or_id] ?? array_shift($items);
    }
    public function hasGlobal($location, $name_or_id): bool
    {
        $items = array_filter($this->globals[$location], fn ($item) => $name_or_id === $item->name);
        return !is_null($this->globals[$location][$name_or_id] ?? array_shift($items));
    }
}