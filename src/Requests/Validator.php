<?php

namespace Lima\Requests;

class Validator
{
    protected $rules = [];
    protected $input = [];
    protected $errors = [];

    public function __construct($input)
    {
        $this->input = $input;
    }

    protected function getInput(): array
    {
        $validatedInput = [];

        foreach ($this->rules as $inputName => $rule) {
            $ruleData = explode('|', $rule);
            $inputType = $ruleData[0];

            if ($this->validateType($inputName, $this->input[$inputName], $inputType, $ruleData[1])) {
                $validatedInput[$inputName] = $this->input[$inputName];
            }
        }

        return $validatedInput;
    }

    protected function getErrors(): array
    {
        return $this->errors;
    }

    private function addError($name, $message)
    {
        $this->errors[$name] = $message;
    }

    private function getProps($rule)
    {
        $ruleProps = explode(',', $rule);
        $props = [];

        foreach ($ruleProps as $prop) {
            $propData = explode(':', $prop);

            if (count($propData) == 1) {
                $props[$propData] = true;
            } else {
                $props[$propData[0]] = $propData[1];
            }
        }

        return $props;
    }

    private function validateType($name, $input, $type, $rule): bool
    {
        $ruleProps = $this->getProps($rule);

        switch ($type) {
            case 'text':
                return $this->validateText($name, $input, $ruleProps);
        }

        return false;
    }

    private function validateText($name, $input, $props): bool
    {
        if (!empty($props['min'])) {
            if (strlen($input) < (int) $props['min']) {
                $this->addError($name, 'must be longer than ' . $props['min'] . ' characters');
                return false;
            }
        }

        if (!empty($props['max'])) {
            if (strlen($input) > (int) $props['max']) {
                $this->addError($name, 'must be shorter than ' . $props['max'] . ' characters');
                return false;
            }
        }

        return true;
    }

    private function validateInt($name, $input, $props): bool
    {
        if (!is_numeric($input)) {
            return false;
        }

        $input = (int) $input;

        if (!empty($props['min'])) {
            if ($input < $props['min']) {
                $this->addError($name, 'must be larger than ' . $props['min']);
                return false;
            }
        }

        if (!empty($props['max'])) {
            if ($input > $props['max']) {
                $this->addError($name, 'must be smaller than ' . $props['max']);
                return false;
            }
        }

        return true;
    }

    private function validateFloat($name, $input, $props): bool
    {
        if (!is_float($input)) {
            return false;
        }

        $input = (float) $input;

        if (!empty($props['min'])) {
            if ($input < $props['min']) {
                $this->addError($name, 'must be larger than ' . $props['min']);
                return false;
            }
        }

        if (!empty($props['max'])) {
            if ($input > $props['max']) {
                $this->addError($name, 'must be smaller than ' . $props['max']);
                return false;
            }
        }

        return true;
    }

    private function validateArray($name, $input, $props): bool
    {
        if (!is_array($input)) {
            return false;
        }

        if (!empty($props['min'])) {
            if (count($input) < $props['min']) {
                $this->addError($name, 'must contain at least ' . $props['min'] . ' elements');
                return false;
            }
        }

        if (!empty($props['max'])) {
            if (count($input) > $props['max']) {
                $this->addError($name, 'must have a max of ' . $props['max'] . ' elements');
                return false;
            }
        }

        return true;
    }
}