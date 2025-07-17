<?php

namespace Docsy\Traits;

trait HasID
{
    public string $id;

    public function setID(string $id = null): static
    {
        $this->id = $id ?? str_replace('.','_',uniqid(strtoupper(basename(get_class($this))) . '_', true));
        return $this;
    }
}