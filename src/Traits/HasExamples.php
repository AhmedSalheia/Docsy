<?php

namespace Docsy\Traits;

use Docsy\Support\Example;

trait HasExamples
{
    /** @var Example[] */
    public array $examples = [];

    public function setExamples(array $examples): static
    {
        $this->examples = array_merge($this->examples, Example::fromArrayCollection($this, ...$examples));
        return $this;
    }
    public function addExample(Example $example): static
    {
        $this->examples[$example->id] = $example;
        return $this;
    }
    public function getExampleByName(string $example_name): Example
    {
        $examples = array_filter($this->examples, function ($item) use ($example_name) {
            return $item->name === $example_name;
        });

        return array_shift($examples);
    }
    public function getExample(string $example_name_or_id = ''): Example | null
    {
        if (!$this->hasExample($example_name_or_id)) return null;
        return array_key_exists($example_name_or_id, $this->examples) ?
            $this->examples[$example_name_or_id] :
            $this->getExampleByName($example_name_or_id);
    }
    public function hasExample(string $example_name_or_id): bool
    {
        return array_key_exists($example_name_or_id, $this->examples) || !is_null($this->getExampleByName($example_name_or_id));
    }
    public function removeExample(string $example_name_or_id): static
    {
        if ($this->hasExample($example_name_or_id)) {
            $example = $this->getExample($example_name_or_id);
            $example->destroy();
            unset($this->examples[$example->id]);
        }
        return $this;
    }
    
}