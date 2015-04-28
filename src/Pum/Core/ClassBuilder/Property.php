<?php

namespace Pum\Core\ClassBuilder;

class Property
{
    const VISIBILITY_PROTECTED = 'protected';
    const VISIBILITY_PRIVATE   = 'private';
    const VISIBILITY_PUBLIC    = 'public';

    protected $name;
    protected $defaultValue;
    protected $visibility;
    protected $isStatic;

    public function __construct($name = null, $defaultValue = null, $visibility = self::VISIBILITY_PROTECTED, $isStatic = false)
    {
        $this->setName($name);
        $this->setDefaultValue($defaultValue);
        $this->setVisibility($visibility);
        $this->setIsStatic($isStatic);
    }

    public static function create($name = null, $defaultValue = null, $visibility = self::VISIBILITY_PROTECTED, $isStatic = false)
    {
        return new self($name, $defaultValue, $visibility, $isStatic);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $name)) {
            throw new \RuntimeException(sprintf('Invalid property name: %s', $name));
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

    public function getVisibility()
    {
        return $this->visibility;
    }

    public function setVisibility($visibility)
    {
        $visibilities = array(self::VISIBILITY_PROTECTED, self::VISIBILITY_PUBLIC, self::VISIBILITY_PRIVATE);
        if (!in_array($visibility, $visibilities)) {
            throw new \RuntimeException(sprintf('Invalid visibility "%s". Valid values are : "%s".', $visibility, implode(', ', $visibilities)));
        }

        $this->visibility = $visibility;

        return $this;
    }

    public function getIsStatic()
    {
        return $this->isStatic;
    }

    public function setIsStatic($isStatic)
    {
        $this->isStatic = (boolean)$isStatic;

        return $this;
    }

    public function getCode()
    {
        $code = '

    /**
     * @var $'.$this->getName().'
     */';

        $code .= '
    '.$this->getVisibility();

        if ($this->getIsStatic()) {
            $code .= ' static';
        }
        
        $code .= ' $'.$this->getName();

        if (!is_null($this->getDefaultValue())) {
            $code .= ' = '.var_export($this->getDefaultValue(), true);
        }

        $code .= ';';

        return $code;
    }
}
