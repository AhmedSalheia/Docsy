<?php

namespace Ahmedsalheia\Docsy;

use Ahmedsalheia\Docsy\Enums\ParamLocation;

class DocsyParam {
    public string $name;
    public ParamLocation $in;

    public string $description = '';
    public string $type = '';
    public bool $required = false;
    public mixed $example = null;

    public function __construct(string $name, ParamLocation $in, string $description = '', string $type = '', bool $required = false, mixed $example = null) {
        $this->name = $name;
        $this->in = $in;
        $this->description = $description;
        $this->type = $type;
        $this->required = $required;
        $this->example = $example;
    }
}