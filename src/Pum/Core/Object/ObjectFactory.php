<?php

namespace Pum\Core\Object;

use Pum\Core\Definition\ObjectDefinition;

/**
 * Responsible of generating entities from object definitions.
 */
class ObjectFactory
{
    /**
     * @var string|null
     */
    private $cacheDir;

    /**
     * @var string|null
     */
    private $projectName;

    /**
     * @var array
     */
    private $classNames = array();

    /**
     * Constructs the class generator.
     *
     * To disable cache, pass ``null`` as cache directory.
     *
     * @param string|null $cacheDir
     */
    public function __construct($projectName, $cacheDir = null)
    {
        $this->projectName = $projectName;
        $this->cacheDir    = $cacheDir;
    }

    /**
     * Verifies cache and returns the class name for given entity name.
     *
     * @return string|false returns classname if it was already generated, returns false if not generated.
     */
    public function isGenerated($name)
    {
        $className = $this->getClassName($name);

        $this->classNames[$className] = $name;

        if (class_exists($className)) {
            return $className;
        }

        if (null === $this->cacheDir) {
            return false;
        }

        $file = $this->cacheDir.'/'.$className;
        if (is_file($file)) {
            require_once $file;
            $this->classNames[$className] = $name;

            return $className;
        }

        return false;
    }

    public function getNameFromClass($className)
    {
        if (isset($this->classNames[$className])) {
            return $this->classNames[$className];
        }

        throw new \InvalidArgumentException(sprintf('Never heard of class "%s".', $className));
    }

    /**
     * Generates a class from an object definition.
     *
     * @return string classname
     */
    public function generate(ObjectDefinition $definition)
    {
        $className = $this->getClassName($definition->getName());
        $extend = $definition->getClassname() ? $definition->getClassname() : '\Pum\Core\Object\Object';
        $class = 'class '.$className.' extends '.$extend.' {'."\n";

        $val = array('id' => 'integer');
        foreach ($definition->getFields() as $field) {
            $val[$field->getName()] = $field->getType();
        }

        // method to load type objects in the entity
        $class .= '    public function __pum__setTypes(\Pum\Core\Type\Factory\TypeFactoryInterface $factory) {'."\n";
        $class .= '        $this->__pum__setTypeInstances(array_map(function ($name) use ($factory) { '."\n";
        $class .= '            return $factory->getType($name); '."\n";
        $class .= '        }, '.var_export($val, true).'));'."\n";
        $class .= '    }'."\n";

        $class .= '}';

        if (null === $this->cacheDir) {
            if (false === eval($class)) {
                throw new \RuntimeException(sprintf('Error while evaluating "%s".', $class));
            }

            return $className;
        }


        $file = $this->cacheDir.'/'.$className;
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($file, '<?php '.$class);

        require_once $file;

        $this->classNames[$className] = $definition->getName();

        return $className;
    }

    /**
     * Returns classname from an entity name.
     *
     * @return string
     */
    public function getClassName($name)
    {
        return 'obj__'.md5($this->projectName.'__'.$name);
    }
}
