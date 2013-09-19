<?php

namespace Pum\Core\ClassBuilder;

class ClassBuilder
{
    protected $className;
    protected $extends    = null;
    protected $implements = array();
    protected $constants  = array();
    protected $properties = array();
    protected $methods    = array();

    public function __construct($className = null)
    {
        $this->setClassName($className);
    }

    /*
     *
     * ClassName Stuff
     *
     */
    public function getClassName()
    {
        return $this->className;
    }

    public function setClassName($className)
    {
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $className)) {
            throw new \RuntimeException(sprintf('Unvalid class name'));
        }

        $this->className = $className;

        return $this;
    }

    /*
     *
     * Extends Stuff
     *
     */
    public function setExtends($extends)
    {
        $this->extends = $extends;

        return $this;
    }

    public function getExtends()
    {
        return $this->extends;
    }

    /*
     *
     * Implements Stuff
     *
     */
    public function hasImplements($interfaceName)
    {
        foreach ($this->implements as $name) {
            if ($interfaceName == $name) {
                return true;
            }
        }

        return false;
    }

    public function getImplements()
    {
        return $this->implements;
    }

    public function addImplements($interfaceName)
    {
        if ($this->hasImplements($interfaceName)) {
            throw new \RuntimeException(sprintf('Interface "%s" already exists.', $interfaceName));
        }

        $this->implements[] = $interfaceName;

        return $this;
    }

    public function removeImplements($interfaceName)
    {
        foreach ($this->implements as $key => $name) {
            if ($interfaceName == $name) {
                unset($this->implements[$key]);
            }
        }

        return $this;
    }

    /*
     *
     * Constants Stuff
     *
     */
    public function hasConstant($constantName)
    {
        foreach ($this->constants as $constant) {
            if ($constant->getName() == $constantName) {
                return true;
            }
        }

        return false;
    }

    public function getConstants()
    {
        return $this->constants();
    }

    public function addConstant(Constant $constant)
    {
        $constantName = $constant->getName();
        if ($this->hasConstant($constantName)) {
            throw new \RuntimeException(sprintf('Constant "%s" already exists.', $constantName));
        }

        $this->constants[] = $constant;

        return $this;
    }

    public function removeConstant($constantName)
    {
        if (isset($this->constants[$constantName])) {
            unset($this->constants[$constantName]);
        }

        return $this;
    }

    public function createConstant($name = null, $defaultValue = null)
    {
        $this->addConstant(Constant::create($name, $defaultValue));

        return $this;
    }

    /*
     *
     * Properties Stuff
     *
     */
    public function hasProperty($propertyName)
    {
        foreach ($this->properties as $property) {
            if ($property->getName() == $propertyName) {
                return true;
            }
        }

        return false;
    }

    public function getProperties()
    {
        return $this->properties();
    }

    public function addProperty(Property $property)
    {
        $propertyName = $property->getName();
        if ($this->hasProperty($propertyName)) {
            throw new \RuntimeException(sprintf('Property "%s" already exists.', $propertyName));
        }

        $this->properties[] = $property;

        return $this;
    }

    public function removeProperty($propertyName)
    {
        if (isset($this->properties[$propertyName])) {
            unset($this->properties[$propertyName]);
        }

        return $this;
    }

    public function createProperty($name = null, $defaultValue = null, $visibility = Property::VISIBILITY_PROTECTED, $isStatic = false)
    {
        $this->addProperty(Property::create($name, $defaultValue, $visibility, $isStatic));

        return $this;
    }

    /*
     *
     * Methods Stuff
     *
     */
    public function hasMethod($methodName)
    {
        foreach ($this->methods as $method) {
            if ($method->getName() == $methodName) {
                return true;
            }
        }

        return false;
    }

    public function getMethods()
    {
        return $this->methods();
    }

    public function addMethod(Method $method)
    {
        $methodName = $method->getName();
        if ($this->hasMethod($methodName)) {
            throw new \RuntimeException(sprintf('Method "%s" already exists.', $methodName));
        }

        $this->methods[] = $method;

        return $this;
    }

    public function removeMethod($methodName)
    {
        if (isset($this->methods[$methodName])) {
            unset($this->methods[$methodName]);
        }

        return $this;
    }

    public function createMethod($name = null, $arguments = null, $body = null, $visibility = Method::VISIBILITY_PUBLIC, $isStatic = false)
    {
        $this->addMethod(Method::create($name, $arguments, $body, $visibility, $isStatic));

        return $this;
    }

    /*
     *
     * Class Stuff
     *
     */
    public function getCode()
    {
        $code = 'class '.$this->getClassName();

        if (!is_null($this->getExtends())) {
            $code .= ' extends '.$this->getExtends();
        }

        if (!empty($this->implements)) {
            $code .= ' '.implode(',', $this->implements);
        }

        $code .= '
{';

        foreach ($this->constants as $obj) {
            $code .= $obj->getCode();
        }

        foreach ($this->properties as $obj) {
            $code .= $obj->getCode();
        }

        foreach ($this->methods as $obj) {
            $code .= $obj->getCode();
        }

        $code .= '
}';

        return $code;
    }

    public function getSample($debug = false)
    {
        $className = $this->className;
        $this->setClassName($newClass = 'tmp_'.md5(uniqid().microtime()));
        $code = $this->getCode();
        if ($debug) {
            echo($code);
            exit;
        }
        eval($code);
        $this->setClassName($className);

        return new $newClass;
    }
}
