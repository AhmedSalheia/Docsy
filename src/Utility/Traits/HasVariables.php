<?php

namespace Docsy\Utility\Traits;

use Docsy\Utility\Variable;

trait HasVariables
{
    protected array $variables = [];
    public function addVariable(array|Variable $variable): static
    {
        $name_or_id = is_array($variable)? $variable['name'] : $variable->id;
        if (!$this->hasVariable($name_or_id)) {
            if (is_array($variable))
                $variable = Variable::fromArray($variable,$this);

            $variable->setParent($this);
            $this->variables[$variable->id] = $variable;
        } else {
            $this->editVariable($variable);
        }
        return $this;
    }
    public function addVariables(array $variables): static
    {
        $this->variables = array_merge($this->variables, Variable::fromArrayCollection($this, ...$variables));

        return $this;
    }
    public function hasVariable(string $var_name_or_id): bool
    {
        return array_key_exists($var_name_or_id, $this->variables) || !is_null($this->getVariableByName($var_name_or_id));
    }

    public function editVariable(array | Variable $variable): static
    {
        $name_or_id = is_array($variable)? $variable['name'] : $variable->id;
        if ($this->hasVariable($name_or_id)) {
            $var = $this->getVariable($name_or_id);
            $var->setValues($variable);
        }

        return $this;
    }
    public function getVariable(string $variable_name_or_id): ?Variable
    {
        if (!$this->hasVariable($variable_name_or_id)) return null;
        return array_key_exists($variable_name_or_id, $this->variables)?
            $this->variables[$variable_name_or_id] :
            $this->getVariableByName($variable_name_or_id);
    }
    private function getVariableByName(string $variable_name) : Variable|array|null
    {
        $variables = array_filter($this->variables, function(Variable $var) use ($variable_name){
            return $var->name == $variable_name;
        });

        return count($variables) > 1 ? $variables : array_shift($variables);
    }
    public function getVariables(): array
    {
        return $this->variables;
    }
}