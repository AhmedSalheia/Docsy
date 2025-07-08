<?php

namespace Ahmedsalheia\Docsy\traits;

use Ahmedsalheia\Docsy\DocsyCollection;
use Ahmedsalheia\Docsy\DocsyFolder;

trait HasParent
{
    private DocsyCollection | DocsyFolder $parent;

    public function getParent(): DocsyCollection | DocsyFolder
    {
        return $this->parent;
    }

    public function setParent(DocsyCollection | DocsyFolder $parent) : static
    {
        $this->parent = $parent;
        return $this;
    }
}