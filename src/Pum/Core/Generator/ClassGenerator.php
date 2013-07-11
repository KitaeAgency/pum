<?php

namespace Pum\Core\Generator;
use Pum\Core\Definition\ObjectDefinition;

class ClassGenerator
{
    private $cacheDir;

    private $classNames;

    public function __construct($cacheDir = null)
    {
        $this->cacheDir = $cacheDir;
    }

    public function isGenerated($name)
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

    public function generate(ObjectDefinition $definition)
    {
        $className = $this->getClassName($definition->getName());

        $class = 'class '.$className.' extends \Pum\Core\Object\Object { ';

        foreach ($definition->getFields() as $field) {
            $class .= 'protected $'.$field->getName().'; ';
        }

        $class .= '}';

        if (null === $this->cacheDir) {
            eval($class);

            return $className;
        }

        $file = $this->cacheDir.'/'.$className;
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($file, '<?php '.$class);

        require_once $file;

        return $className;
    }

    public function getClassName($name)
    {
        return 'pum_object_'.md5($name);
    }
}
