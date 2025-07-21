<?php

namespace Docsy\Traits;

trait HasMeta
{
    protected array $meta = [];

    public function meta($key = null, $default = null)
    {
        return $this->meta[$key] ?? $default;
    }

    public function setMeta($key, $value = null) : static
    {
        $this->meta[$key] = $value;
        return $this;
    }
}