<?php

namespace Ahmedsalheia\Docsy\traits;

use Ahmedsalheia\Docsy\DocsyParam;
use Ahmedsalheia\Docsy\Enums\ParamLocation;

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
        $this->globals['headers'][$header] = new DocsyParam($header, ParamLocation::Header, $description, required: $required, example: $value);
        return $this;
    }
    public function addGlobalQueryParam($query, $value, string $description = '', bool $required = false): static
    {
        $this->globals['query'][$query] = new DocsyParam($query, ParamLocation::Query, $description, required: $required, example: $value);
        return $this;
    }
    public function addGlobalBodyParam($bodyParam, $value, string $description = '', bool $required = false): static
    {
        $this->globals['body'][$bodyParam] = new DocsyParam($bodyParam, ParamLocation::Body, $description, required: $required, example: $value);
        return $this;
    }
}