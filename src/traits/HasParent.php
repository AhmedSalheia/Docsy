<?php

namespace Ahmedsalheia\Docsy\traits;

use Ahmedsalheia\Docsy\DocsyCollection;
use Ahmedsalheia\Docsy\DocsyFolder;

trait HasParent
{
    private DocsyCollection | DocsyFolder | null $parent = null;

    public function getParent(): DocsyCollection | DocsyFolder | null
    {
        return $this->parent;
    }

    public function setParent(DocsyCollection | DocsyFolder $parent) : static
    {
        $this->parent = $parent;
        return $this;
    }
}