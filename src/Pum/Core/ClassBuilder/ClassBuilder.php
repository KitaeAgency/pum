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
            throw new \RuntimeException(sprintf('Invalid class name'));
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
        foreach ($this->implements as $k => $name) {
            if ($interfaceName == $name) {
                unset($this->implements[$k]);
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

    public function getConstant($constantName)
    {
        foreach ($this->constants as $constant) {
            if ($constant->getName() == $constantName) {
                return $constant;
            }
        }

        throw new \RuntimeException(sprintf('Constant "%s" do not exists.', $constantName));
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
        foreach ($this->constants as $k => $constant) {
            if ($constant->getName() == $constantName) {
                unset($this->constants[$k]);
            }
        }

        return $this;
    }

    public function createConstant($name = null, $defaultValue = null)
    {
        return $this->addConstant(Constant::create($name, $defaultValue));
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

    public function getProperty($propertyName)
    {
        foreach ($this->properties as $property) {
            if ($property->getName() == $propertyName) {
                return $property;
            }
        }

        throw new \RuntimeException(sprintf('Property "%s" do not exists.', $propertyName));
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
        foreach ($this->properties as $k => $property) {
            if ($property->getName() == $propertyName) {
                unset($this->properties[$k]);
            }
        }

        return $this;
    }

    public function createProperty($name = null, $defaultValue = null, $visibility = Property::VISIBILITY_PROTECTED, $isStatic = false)
    {
        return $this->addProperty(Property::create($name, $defaultValue, $visibility, $isStatic));
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

    public function getMethod($methodName)
    {
        foreach ($this->methods as $method) {
            if ($method->getName() == $methodName) {
                return $method;
            }
        }

        throw new \RuntimeException(sprintf('Method "%s" do not exists.', $methodName));
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
        foreach ($this->methods as $k => $method) {
            if ($method->getName() == $methodName) {
                unset($this->methods[$k]);
            }
        }

        return $this;
    }

    public function createMethod($name = null, $arguments = null, $body = null, $visibility = Method::VISIBILITY_PUBLIC, $isStatic = false)
    {
        return $this->addMethod(Method::create($name, $arguments, $body, $visibility, $isStatic));
    }

    public function addGetMethod($propertyName)
    {
        if (!$this->hasProperty($propertyName)) {
            throw new \RuntimeException(sprintf('AddGetMethod : property "%s" do not exists.', $propertyName));
        }

        return $this->createMethod('get'.ucfirst($propertyName), '', 'return $this->'.$propertyName.';');
    }

    public function addSetMethod($propertyName)
    {
        if (!$this->hasProperty($propertyName)) {
            throw new \RuntimeException(sprintf('AddSetMethod : property "%s" do not exists.', $propertyName));
        }

        return $this->createMethod('set'.ucfirst($propertyName), '$'.$propertyName, '$this->'.$propertyName.' = $'.$propertyName.'; return $this;');
    }

    public function prependOrCreateMethod($method, $arguments, $code)
    {
        if ($this->hasMethod($method)) {
            $method = $this->getMethod($method);
            if ($method->getArguments() !== $arguments) {
                throw new \InvalidArgumentException(sprintf('Signatures are different: "%s" and "%s".', $method->getArguments(), $arguments));
            }
            $method->prependCode($code);
        } else {
            $this->createMethod($method, $arguments, $code);
        }
    }

    /*
     *
     * Class Stuff
     *
     */
    public function validateCode()
    {
        $className = $this->className;
        $this->setClassName($newClass = 'tmp_'.md5(uniqid().microtime()));

        $code = $this->getCode(false);
        if (@eval($code) === false) {
            $error = '';
            foreach (error_get_last() as $key => $value) {
                $error .= '
'.$key.' : '.$value.'';
            }
            throw new \RuntimeException(sprintf('Php code error : "%s". Source:'."\n".$this->addLines($code), $error));
        }

        $this->setClassName($className);
    }

    public function getCode($validate = true)
    {
        if ($validate) {
            $this->validateCode();
        }

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
            echo $code;
            exit;
        }
        eval($code);

        $this->setClassName($className);

        return new $newClass;
    }

    private function addLines($code)
    {
        $code  = explode("\n", $code);
        $count = count($code);
        $width = strlen($count) + 1;

        $result = '';
        for ($i = 1; $i <= $count; $i++) {
            $result .= sprintf('%-'.$width.'s%s', $i, $code[$i - 1])."\n";
        }

        return $result;
    }
}
