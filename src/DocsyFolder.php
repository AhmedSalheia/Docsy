<?php

namespace Ahmedsalheia\Docsy;

use Ahmedsalheia\Docsy\traits\HasGlobals;
use Ahmedsalheia\Docsy\traits\HasParent;

class DocsyFolder
{
    use HasGlobals, HasParent;

    public string $name;
    public string $description;
    public bool $requires_auth = false;
    public array $content = [];

    public function __construct(string $name, string $description = '', bool $requires_auth = false)
    {
        $this->name = $name;
        $this->description = $description;
        $this->requires_auth = $requires_auth;
    }

    public function add(DocsyRequest | DocsyFolder $data): static
    {

        $data->setParent($this);
        $data->requires_auth = $this->requires_auth;
        $this->content[] = $data;

        return $this;
    }
}
