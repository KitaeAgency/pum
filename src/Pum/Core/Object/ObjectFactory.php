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
     * @return string
     */
    public function getProjectName()
    {
        return $this->projectName;
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

        $types = array();
        $options = array();
        foreach ($definition->getFields() as $field) {
            $types[$field->getName()] = $field->getType();
            $options[$field->getName()] = $field->getTypeOptions();
        }

        $types   = var_export($types, true);
        $options = var_export($options, true);

        // method to load type objects in the entity
        $class = <<<CLASS
/**
 * Class for definition "{$definition->getName()}" in project "{$this->projectName}".
 */
class $className extends $extend
{
    public function __pum__initialize(\Pum\Core\Type\Factory\TypeFactoryInterface \$factory)
    {
        \$metadata = new \Pum\Core\Object\ObjectMetadata;
        \$metadata->typeFactory = \$factory;
        \$metadata->types = $types;
        \$metadata->typeOptions = $options;
        \$this->__pum_setMetadata(\$metadata);
    }
}
CLASS;

        $file = $this->cacheDir.'/'.$className;
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($file, '<?php '.$class);

        require_once $file;

        return $className;
    }

    /**
     * Returns classname from an entity name.
     *
     * @return string
     */
    public function getClassName($name)
    {
        $class = 'obj__'.md5($this->projectName.'__'.$name);

        $this->classNames[$class] = $name;

        return $class;
    }

    public function clearCache()
    {
        if (count($this->classNames)) {
            throw new \RuntimeException(sprintf('Unable to clear object factory cache: already loaded: "%s".', implode(', ', $this->classNames)));
        }

        if (!is_dir($this->cacheDir)) {
            return;
        }

        foreach (new \DirectoryIterator($this->cacheDir) as $file) {
            if ($file->isDot()) {
                continue;
            }

            unlink($file->getPathname());
        }
    }
}
