<?php

namespace Docsy\Utility\Traits;

use Docsy\Collection;
use Docsy\Folder;
use Docsy\Request;
use Docsy\Utility\Example;

trait HasParent
{
    private Collection | Folder | Request | Example | null $parent = null;

    public function getParent(): Collection | Folder | Request | Example | null
    {
        return $this->parent;
    }
    public function setParent(Collection | Folder | Request | Example | null $parent) : static
    {
        $this->parent = $parent;
        return $this;
    }

    public function getChain(): string
    {
        $chain = [];
        $current = $this->getParent();
        while(method_exists($current, 'getParent'))
        {
            $chain[] = $current->id;
            $current = $current->getParent();
        }
        $chain[] = $current->id;
        return implode(',', array_reverse($chain));
    }
}