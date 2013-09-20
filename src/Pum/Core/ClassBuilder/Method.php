<?php

namespace Pum\Core\ClassBuilder;

class Method
{
    const VISIBILITY_PROTECTED = 'protected';
    const VISIBILITY_PRIVATE   = 'private';
    const VISIBILITY_PUBLIC    = 'public';

    protected $name;
    protected $visibility;
    protected $arguments;
    protected $body;
    protected $isStatic;

    protected $prependCode = array();
    protected $appendCode  = array();

    public function __construct($name = null, $arguments = null, $body = null, $visibility = self::VISIBILITY_PUBLIC, $isStatic = false)
    {
        $this->setName($name);
        $this->setArguments($arguments);
        $this->setBody($body);
        $this->setVisibility($visibility);
        $this->setIsStatic($isStatic);
    }

    public static function create($name = null, $arguments = null, $body = null, $visibility = self::VISIBILITY_PUBLIC, $isStatic = false)
    {
        return new self($name, $arguments, $body, $visibility, $isStatic);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $name)) {
            throw new \RuntimeException(sprintf('Unvalid method name'));
        }

        $this->name = $name;

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
            throw new \RuntimeException(sprintf('Unvalid visibility "%s". Valid values are : "%s".', $visibility, implode(', ', $visibilities)));
        }

        $this->visibility = $visibility;

        return $this;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function setArguments($arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;

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

    public function prependCode($prependCode)
    {
        array_unshift($this->prependCode, $prependCode);

        return $this;
    }

    public function removePrependCode()
    {
        $this->prependCode = array();

        return $this;
    }

    public function appendCode($appendCode)
    {
        $this->appendCode[] = $appendCode;

        return $this;
    }

    public function removeAppendCode()
    {
        $this->appendCode = array();

        return $this;
    }

    public function getCode()
    {
        $code = '

    /**
     * @method '.$this->getName().'
     */';

        $code .= '
    '.$this->getVisibility();

        if ($this->getIsStatic()) {
            $code .= ' static';
        }
        
        $code .= ' function '.$this->getName();

        if (!is_null($this->getArguments())) {
            $code .= '('.$this->getArguments().')';
        } else {
            $code .= '()';
        }

        $code .= '
    {';

        foreach ($this->prependCode as $value) {
            $code .= '
        '.$value;
        }

        if (!is_null($this->getBody())) {
            $code .= '
        '.$this->getBody();
        }

        foreach ($this->appendCode as $value) {
            $code .= '
        '.$value;
        }

        $code .= '
    }';

        return $code;
    }
}
