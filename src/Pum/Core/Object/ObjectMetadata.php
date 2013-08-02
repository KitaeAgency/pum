<?php

namespace Pum\Core\Object;

/**
 * Metadata informations for an Object.
 */
class ObjectMetadata
{
    /**
     * @var Pum\Core\Type\Factory\TypeFactoryInterface
     */
    public $typeFactory;

    /**
     * @var string
     */
    public $tableName;

    /**
     * @var array an associative array of field type.
     */
    public $types = array();

    /**
     * @var array an associative array of field options.
     */
    public $typeOptions = array();

    /**
     * @var array an associative array of relation informations.
     */
    public $relations = array();

    public function hasField($name)
    {
        return isset($this->types[$name]);
    }

    public function getType($name)
    {
        if (!$this->hasField($name)) {
            throw new \RuntimeException(sprintf('No field named "%s". Available: "%s".', $name, implode(', ', array_keys($this->types))));
        }

        return $this->typeFactory->getType($this->types[$name]);
    }

    public function writeValue(Object $object, $name, $value)
    {
        $this->getType($name)->writeValue($object, $value, $name, $this->typeOptions[$name]);
    }

    public function readValue(Object $object, $name)
    {
        return $this->getType($name)->readValue($object, $name, $this->typeOptions[$name]);
    }
}
