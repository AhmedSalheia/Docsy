<?php

namespace Docsy\Traits;

trait CouldBeDisabled
{
    public bool $disabled = false;
    public function is_disabled($disabled = null) : static
    {
        $this->disabled = $disabled ?? $this->disabled;
        return $this;
    }
}