<?php

namespace Docsy\Utility\Traits;

use Docsy\Utility\Enums\ParamLocation;
use Docsy\Utility\Param;

trait HasParams
{
    public array $pathParams = [];
    public array $queryParams = [];
    public array $headerParams = [];
    public array $bodyParams = [];
    public array $cookieParams = [];

    public function getParams(ParamLocation $paramLocation): array
    {
        return $this->getParamsArray($paramLocation);
    }
    private function getParamsArray(ParamLocation $paramLocation) : array
    {
        return $this->{$paramLocation->value . 'Params'};
    }
    public function hasParam(ParamLocation $paramLocation, string | Param | array $name_or_id) : bool
    {
        $name_or_id = is_a($name_or_id, Param::class) ?
            $name_or_id->id :
            (
                is_array($name_or_id) ?
                    $name_or_id['name'] :
                    $name_or_id
            );

        return isset($this->getParamsArray($paramLocation)[$name_or_id]) || $this->hasParamName($paramLocation, $name_or_id);
    }
    public function hasParamName(ParamLocation $paramLocation,$name) : bool
    {
        $params = array_filter($this->getParamsArray($paramLocation), fn ($param) => $param->name === $name);
        return count($params) > 0;
    }

    private function getParamByName(ParamLocation $paramLocation, $name, int $index = 0) : Param
    {
        if (!$this->hasParam($paramLocation, $name))
            throw new \InvalidArgumentException("{$name} Param doesn\'t exist");

        $params = array_filter($this->getParamsArray($paramLocation), fn ($param) => $param->name === $name);
        if ($index < count($params))
            return array_values($params)[$index];

        else
            throw new \InvalidArgumentException(
                "Index out of bound, {$index} index requested in array<". count($params) .">"
            );

    }
    public function getParam(ParamLocation $paramLocation,string $name_or_id) : Param
    {
        if (!$this->hasParam($paramLocation, $name_or_id))
            throw new \InvalidArgumentException("{$name_or_id} Param doesn\'t exist in " . static::class);

        return $this->hasParamName($paramLocation, $name_or_id) ?
            $this->getParamByName($paramLocation, $name_or_id):
            $this->getParamsArray($paramLocation)[$name_or_id];
    }

    public function addAndReturnParam(ParamLocation $paramLocation, string | Param $name, string $description = '', bool $required = false, mixed $value = null) : Param
    {
        if (is_a($name,Param::class))
            $param = $name;
        else
            $param = (new Param($name, $paramLocation, $description,'', $required, $value));

        $this->addParam($paramLocation, $param);
        return $param;
    }
    public function addParam(ParamLocation $paramLocation, string | Param| array $param, string $description = '', bool $required = false, mixed $value = null) : static
    {
        if ($this->hasParamName($paramLocation,$param_name = is_array($param) ? $param['name'] : (is_string($param) ? $param : $param->name)))
        {
            $param = $this->getParamByName($paramLocation, $param_name);
            $param->description = $description ?: $param->description;
            $param->required = $required ?: $param->required;
            $param->value = $value ?? $param->value;
        }

        if (is_a($param, Param::class)) {
            $param->in = $paramLocation;
            $this->{$paramLocation->value . 'Params'}[$param->id] = $param->setParent($this);
        } elseif (is_array($param)) {
            $param['in'] = $paramLocation;
            $param = Param::fromArray($param);
            $this->{$paramLocation->value . 'Params'}[$param->id] = $param->setParent($this);
        } else {
            $param = (new Param($param, $paramLocation, $description,'', $required, $value))->setParent($this);
            $this->{$paramLocation->value . 'Params'}[$param->id] = $param;
        }

        return $this;
    }
    public function addPathParam(string | Param | array $param, string $description = '', bool $required = false, mixed $value = null) : static
    {
        $this->addParam(ParamLocation::Path, $param, $description, $required, $value);
        return $this;
    }
    public function addQueryParam(string | Param | array $param, string $description = '', bool $required = false, mixed $value = null) : static
    {
        $this->addParam(ParamLocation::Query, $param, $description, $required, $value);
        return $this;
    }
    public function addHeaderParam(string | Param | array $param, string $description = '', bool $required = false, mixed $value = null) : static
    {
        $this->addParam(ParamLocation::Header, $param, $description, $required, $value);
        return $this;
    }
    public function addBodyParam(string | Param | array $param, string $description = '', bool $required = false, mixed $value = null) : static
    {
        $this->addParam(ParamLocation::Body, $param, $description, $required, $value);
        return $this;
    }
    public function addCookieParam(string | Param $param, string $description = '', bool $required = false, mixed $value = null) : static
    {
        $this->addParam(ParamLocation::Cookie, $param, $description, $required, $value);
        return $this;
    }

    public function editParam(ParamLocation $paramLocation, string $param, string $description = '', bool $required = false, mixed $value = null): static
    {
        $param = $this->getParam($paramLocation, $param);
        $param->description = $description ?? $param->description;
        $param->required = $required ?? $param->required;
        $param->setValue($value ?? $param->value);

        return $this;
    }
    public function removeParam(ParamLocation $paramLocation, string $name_or_id) : static
    {
        if ($this->hasParam($paramLocation, $name_or_id))
        {
            $param = $this->getParam($paramLocation, $name_or_id);
            unset($this->{$paramLocation->value . 'Params'}[$param->id]);
        }
        return $this;
    }
}