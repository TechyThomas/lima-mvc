<?php

namespace Lima\Requests;

class Validator
{
    protected $rules = [];
    protected $input = [];
    protected $validatedInput = [];
    protected $errors = [];

    public function __construct($input)
    {
        $this->input = $input;
    }

    public function getInput(): array
    {
        foreach ($this->rules as $inputName => $rule) {
            $ruleData = explode('|', $rule);
            $inputType = $ruleData[0];
            $ruleProps = $ruleData[1] ?? '';

            $this->validateType($inputName, $this->input[$inputName], $inputType, $ruleProps);
        }

        return $this->validatedInput;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function addError($name, $message)
    {
        $this->errors[$name] = $message;
    }

    private function getProps($rule)
    {
        if (empty($rule)) {
            return [];
        }

        $ruleProps = explode(',', $rule);
        $props = [];

        foreach ($ruleProps as $prop) {
            $propData = explode(':', $prop);
            if (empty($propData) || count($propData) <= 1)
                continue;

            $props[$propData[0]] = $propData[1];
        }

        return $props;
    }

    private function validateType($name, $input, $type, $rule): bool
    {
        $ruleProps = $this->getProps($rule);

        switch ($type) {
            case 'text':
            case 'string':
                return $this->validateText($name, $input, $ruleProps);
            case 'int':
                return $this->validateInt($name, $input, $ruleProps);
            case 'float':
                return $this->validateFloat($name, $input, $ruleProps);
            case 'array':
                return $this->validateArray($name, $input, $ruleProps);
            case 'email':
                return $this->validateEmail($name, $input, $ruleProps);
        }

        return false;
    }

    private function validateText($name, $input, $props): bool
    {
        if (gettype($input) != 'string') {
            return false;
        }

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

        $this->validatedInput[$name] = htmlspecialchars($input);

        return true;
    }

    private function validateInt($name, $input, $props): bool
    {
        if (!is_numeric($input)) {
            $this->addError($name, 'is not a number');
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

        $this->validatedInput[$name] = filter_var($input, FILTER_VALIDATE_INT);

        return true;
    }

    private function validateFloat($name, $input, $props): bool
    {
        if (!is_float($input)) {
            $this->addError($name, 'is not a number');
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

        $this->validatedInput[$name] = filter_var($input, FILTER_VALIDATE_FLOAT);

        return true;
    }

    private function validateArray($name, $input, $props): bool
    {
        if (!is_array($input)) {
            $this->addError($name, 'is not an array');
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

        $this->validatedInput[$name] = filter_var($input, FILTER_FORCE_ARRAY);

        return true;
    }

    private function validateEmail($name, $input, $props): bool
    {
        if (gettype($input) != 'string') {
            $this->addError($name, 'is not a string');
            return false;
        }

        if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $this->addError($name, 'is not a valid email address');
            return false;
        }

        $this->validatedInput[$name] = filter_var($input, FILTER_SANITIZE_EMAIL);

        return true;
    }
}