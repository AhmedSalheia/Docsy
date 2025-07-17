<?php

namespace Docsy\Traits;

use Docsy\Enums\ParamLocation;
use Docsy\Support\Param;

trait HasGlobals
{
    /**
     * @var $globals array<string,array>
     */
    private array $globals = [
        'headers' => [],
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
    public function addGlobalHeader($header, $value, string $description = '', bool $required = false): static
    {
        $header = new Param($header, ParamLocation::Header, $description, required: $required, value: $value);
        $this->globals['headers'][$header->id] = $header;
        return $this;
    }
    public function addGlobalQueryParam($query, $value, string $description = '', bool $required = false): static
    {
        $query = new Param($query, ParamLocation::Query, $description, required: $required, value: $value);
        $this->globals['query'][$query->id] = $query;
        return $this;
    }
    public function addGlobalBodyParam($bodyParam, $value, string $description = '', bool $required = false): static
    {
        $bodyParam = new Param($bodyParam, ParamLocation::Body, $description, required: $required, value: $value);
        $this->globals['body'][$bodyParam->id] = $bodyParam;
        return $this;
    }
}