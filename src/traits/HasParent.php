<?php

namespace Ahmedsalheia\Docsy\traits;

use Ahmedsalheia\Docsy\DocsyCollection;
use Ahmedsalheia\Docsy\DocsyFolder;
use Ahmedsalheia\Docsy\DocsyRequest;

trait HasParent
{
    private DocsyCollection | DocsyFolder | DocsyRequest | null $parent = null;

    public function getParent(): DocsyCollection | DocsyFolder | DocsyRequest | null
    {
        return $this->parent;
    }
    public function setParent(DocsyCollection | DocsyFolder | DocsyRequest | null $parent) : static
    {
        $this->parent = $parent;
        return $this;
    }
}