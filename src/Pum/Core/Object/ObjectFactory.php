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

    private $loadingClasses = array();

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
                $class::_pumInitialize($this->schemaManager->getTypeFactory());
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

            $class = $this->getClassName($name);

            if (!isset($this->loadingClasses[$name])) {
                $this->loadingClasses[$name] = true;
                $class = $this->generateClass($definition, $project);
                $class::_pumInitialize($this->schemaManager->getTypeFactory());

                unset($this->loadingClasses[$name]);
            }
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
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('Never heard of class "%s".', $className));
        }

        return $className::__PUM_OBJECT_NAME;
    }

    public function clearCache()
    {
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

            return $className;
        }

        return false;
    }

    /**
     * Generates a class from an object definition.
     *
     * @return string classname
     */
    private function generateClass(ObjectDefinition $definition, Project $project)
    {
        $className = $this->getClassName($definition->getName());
        $extend = $definition->getClassname() ? $definition->getClassname() : '\Pum\Core\Object\Object';
        $class = 'class '.$className.' extends '.$extend.' {'."\n";

        $types = array();
        $options = array();
        $relations = array();
        $fieldDependencies = array();
        foreach ($definition->getFields() as $field) {
            $name        = $field->getName();
            $type        = $field->getType();
            $typeOptions = $field->getTypeOptions();

            $typeInstance = $this->schemaManager->getTypeFactory()->getType($type);

            $types[$name]   = $type;
            $options[$name] = $typeOptions;

            $rawFields = $typeInstance->getRawColumns($name, $typeOptions);
            foreach ($rawFields as $rawField) {
                $fieldDependencies[$rawField] = $name;
            }
        }

        foreach ($project->getRelations() as $relation) {
            $relationTableName = 'assoc__'.$this->safeValue($project->getName().'__'.$relation->getFrom().'_'.$relation->getFromName());

            if ($relation->getFrom() === $definition->getName()) {
                $relations[$relation->getFromName()] = array(
                    'from'      => $relation->getFrom(),
                    'fromName'  => $relation->getFromName(),
                    'to'        => $relation->getTo(),
                    'toName'    => $relation->getToName(),
                    'toClass'   => $this->getClass($relation->getTo()),
                    'type'      => $relation->getType(),
                    'tableName' => $relationTableName,
                );
            }
            if ($relation->getTo() === $definition->getName() && $relation->getToName()) {
                $relations[$relation->getToName()] = array(
                    'from'      => $relation->getTo(),
                    'fromName'  => $relation->getToName(),
                    'to'        => $relation->getFrom(),
                    'toName'    => $relation->getFromName(),
                    'toClass'   => $this->getClass($relation->getFrom()),
                    'type'      => $relation->getReverseType(),
                    'tableName' => $relationTableName,
                );
            }
        }

        $getters     = $this->generateGetters(array_keys($types));
        $stringField = $this->generateObjectString(array_keys($types), $definition->getName() . '_object');
        $types       = var_export($types, true);
        $options     = var_export($options, true);
        $relations   = var_export($relations, true);
        $tableName   = var_export('object_'.$this->safeValue($project->getName().'__'.$definition->getName()), true);
        $fieldDependencies = var_export($fieldDependencies, true);


        // method to load type objects in the entity
        $class = <<<CLASS
/**
 * Class for definition "{$definition->getName()}" in project "{$this->projectName}".
 */
class $className extends $extend
{
    const __PUM_PROJECT_NAME = "{$project->getName()}";
    const __PUM_OBJECT_NAME  = "{$definition->getName()}";

    private static \$__pum_metadata;

    public static function _pumInitialize(\Pum\Core\Type\Factory\TypeFactoryInterface \$factory)
    {
        \$metadata = new \Pum\Core\Object\ObjectMetadata;
        \$metadata->tableName = $tableName;
        \$metadata->typeFactory = \$factory;
        \$metadata->types = $types;
        \$metadata->typeOptions = $options;
        \$metadata->relations = $relations;
        \$metadata->fieldDependencies = $fieldDependencies;
        self::\$__pum_metadata = \$metadata;
    }

    public static function _pumGetMetadata()
    {
        if (null === self::\$__pum_metadata) {
            throw new \RuntimeException('Metadata not loaded in "$className".');
        }

        return self::\$__pum_metadata;
    }

$getters
$stringField
}
CLASS;

        $file = $this->cacheDir.'/'.$className;
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($file, '<?php '.$class);

        require_once $file;

        $className::_pumInitialize($this->schemaManager->getTypeFactory());

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

        return $class;
    }

    private function safeValue($text)
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '_', $text));
    }

    private function generateGetters(array $props)
    {
        $code = '';
        foreach ($props as $prop) {
            $ucProp = ucfirst($prop);
            $code .= <<<GETTER
public function get{$ucProp}()
{
    return \$this->get('$prop');
}

GETTER;
        }

        return $code;
    }

    private function generateObjectString(array $props, $default)
    {
        $fielsString = array(
            'name',
            'title',
            'fullname',
            'firstname',
            'lastname',
        );
        $return = "return '{$default}';";
        foreach ($fielsString as $field) {
            if(in_array($field, $props)) {
                $return = "return \$this->get('{$field}');";
            }
        }

        $code = <<<OBJECTSTRING
public function __toString()
{
    $return
}

OBJECTSTRING;

        return $code;
    }
}
