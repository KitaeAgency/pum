<?php

namespace Pum\Core\Object;

use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\SchemaManager;

/**
 * Responsible of generating entities from object definitions.
 */
class ObjectFactory
{
    const CLASS_PREFIX = 'obj__';

    /**
     * @var string|null
     */
    private $cacheDir;

    /**
     * @var string|null
     */
    private $projectName;

    /**
     * @var Pum\Core\SchemaManager
     */
    private $schemaManager;

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
    public function __construct(SchemaManager $schemaManager, $projectName, $cacheDir = null)
    {
        $this->schemaManager = $schemaManager;
        $this->projectName   = $projectName;
        $this->cacheDir      = $cacheDir;

        // register autoloading of project entities
        spl_autoload_register(function ($class) {
            if (0 !== strpos($class, self::CLASS_PREFIX)) {
                return;
            }

            if (file_exists($this->cacheDir.'/'.$class)) {
                require_once $this->cacheDir.'/'.$class;
                $class::__pum__initialize($this->schemaManager->getTypeFactory());
            }
        });
    }

    /**
     * Instanciates a new PUM object.
     *
     * @return Pum\Core\Object\Object
     *
     * @throws Pum\Core\Exception\DefinitionNotFoundException
     */
    public function createObject($name)
    {
        $class = $this->getClass($name);

        $instance = new $class();

        return $instance;
    }

    /**
     * Returns class (load it if needed) associated to a pum object.
     *
     * @return string
     *
     * @throws Pum\Core\Exception\DefinitionNotFoundException
     */
    public function getClass($name)
    {
        $class = $this->loadClass($name);

        if (false === $class) {

            // costful part
            $project    = $this->schemaManager->getProject($this->projectName);
            $definition = $project->getObject($name);
            $class = $this->generate($definition, $project);

            $class::__pum__initialize($this->schemaManager->getTypeFactory());
        }

        return $class;
    }

    /**
     * @return string
     */
    public function getProjectName()
    {
        return $this->projectName;
    }

    /**
     * Returns object name from classname.
     *
     * @return string
     */
    public function getNameFromClass($className)
    {
        if (isset($this->classNames[$className])) {
            return $this->classNames[$className];
        }

        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('Never heard of class "%s".', $className));
        }

        return $this->classNames[$className] = $className::__PUM_OBJECT_NAME;
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

    /**
     * Verifies cache and returns the class name for given entity name.
     *
     * @return string|false returns classname if it was already generated, returns false if not generated.
     */
    private function loadClass($name)
    {
        $className = $this->getClassName($name);

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

    /**
     * Generates a class from an object definition.
     *
     * @return string classname
     */
    private function generate(ObjectDefinition $definition, Project $project)
    {
        $className = $this->getClassName($definition->getName());
        $extend = $definition->getClassname() ? $definition->getClassname() : '\Pum\Core\Object\Object';
        $class = 'class '.$className.' extends '.$extend.' {'."\n";

        $types = array();
        $options = array();
        $relations = array();
        foreach ($definition->getFields() as $field) {
            $types[$field->getName()] = $field->getType();
            $options[$field->getName()] = $field->getTypeOptions();
        }

        foreach ($project->getRelations() as $relation) {
            if ($relation->getFrom() === $definition->getName()) {
                $relations[$relation->getFromName()] = array(
                    'to'      => $relation->getTo(),
                    'toClass' => $this->getClassname($relation->getTo()),
                    'type'    => $relation->getType(),
                );
            }
            if ($relation->getTo() === $definition->getName() && $relation->getToName()) {
                $relations[$relation->getToName()] = array(
                    'to'      => $relation->getFrom(),
                    'toClass' => $this->getClassname($relation->getTo()),
                    'type'    => $relation->getReverseType()
                );
            }
        }

        $types     = var_export($types, true);
        $options   = var_export($options, true);
        $relations = var_export($relations, true);

        // method to load type objects in the entity
        $class = <<<CLASS
/**
 * Class for definition "{$definition->getName()}" in project "{$this->projectName}".
 */
class $className extends $extend
{
    const __PUM_PROJECT_NAME = "{$project->getName()}";
    const __PUM_OBJECT_NAME  = "{$definition->getName()}";

    public static function __pum__initialize(\Pum\Core\Type\Factory\TypeFactoryInterface \$factory)
    {
        \$metadata = new \Pum\Core\Object\ObjectMetadata;
        \$metadata->typeFactory = \$factory;
        \$metadata->types = $types;
        \$metadata->typeOptions = $options;
        \$metadata->relations = $relations;
        self::__pum_setMetadata(\$metadata);
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

        $className::__pum__initialize($this->schemaManager->getTypeFactory());

        return $className;
    }

    /**
     * Returns classname from an entity name.
     *
     * @return string
     */
    private function getClassName($name)
    {
        $class = self::CLASS_PREFIX.md5($this->projectName.'__'.$name);

        $this->classNames[$class] = $name;

        return $class;
    }
}
