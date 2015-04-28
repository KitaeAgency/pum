<?php

namespace Pum\Core\ClassBuilder;

class Constant
{
    protected $name;
    protected $defaultValue;

    public function __construct($name = null, $defaultValue = null)
    {
        $this->setName($name);
        $this->setDefaultValue($defaultValue);
    }

    public static function create($name = null, $defaultValue = null)
    {
        return new self($name, $defaultValue);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $name)) {
            throw new \RuntimeException(sprintf('Invalid const name: %s', $name));
        }

        $this->name = $name;

        return $this;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function getCode()
    {
        $code = '

    /**
     * @const '.$this->getName().'
     */';

        $code .= '
    const '.$this->getName();

        if (!is_null($this->getDefaultValue())) {
            $code .= ' = '.var_export($this->getDefaultValue(), true);
        } else {
            $code .= ' = ""';
        }

        $code .= ';';

        return $code;
    }
}
