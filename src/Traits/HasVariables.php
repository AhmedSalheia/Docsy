<?php

namespace Docsy\Traits;

use Docsy\Support\Variable;

trait HasVariables
{
    protected array $variables = [];
    public function addVariable(array|Variable $variable): static
    {
        if (is_array($variable))
            $variable = Variable::fromArray($variable,$this);

        $variable->setParent($this);
        $this->variables[$variable->id] = $variable;
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
    public function getVariable(string $variable_name_or_id): ?Variable
    {
        if (!$this->hasVariable($variable_name_or_id)) return null;
        return array_key_exists($variable_name_or_id, $this->variables)?
            $this->variables[$variable_name_or_id] :
            $this->getVariableByName($variable_name_or_id);
    }
    private function getVariableByName(string $variable_name) : Variable
    {
        $variables = array_filter($this->variables, function(Variable $var) use ($variable_name){
            return $var->name == $variable_name;
        });
        return array_shift($variables);
    }
    public function getVariables(): array
    {
        return $this->variables;
    }
}