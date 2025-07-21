<?php

namespace Docsy\Traits;

trait HasMeta
{
    protected array $meta = [];

    public function meta(string $key = null, mixed $default = null)
    {
        return $this->meta[$key] ?? $default;
    }

    public function setMeta(string | array $key, mixed $value = null) : static
    {
        if (is_array($key))
            $this->meta = array_merge($this->meta, $key);
        else
            $this->meta[$key] = $value;

        return $this;
    }
}