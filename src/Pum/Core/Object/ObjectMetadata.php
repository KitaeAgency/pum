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

    /**
     * @var array an associative array from raw column name to model column name.
     */
    public $fieldDependencies = array();

    public function hasRawField($name)
    {
        return 'id' === $name || isset($this->fieldDependencies[$name]) || isset($this->relations[$name]);
    }

    public function hasField($name)
    {
        return isset($this->types[$name]);
    }

    public function getType($field)
    {
        return $this->typeFactory->getType($this->getTypeName($field));
    }

    public function getTypeName($field)
    {
        if (!$this->hasField($field)) {
            throw new \RuntimeException(sprintf('No field named "%s". Available: "%s".', $field, implode(', ', array_keys($this->types))));
        }

        return $this->types[$field];
    }

    public function getTypeOptions($name)
    {
        if (!isset($this->typeOptions[$name])) {
            throw new \RuntimeException(sprintf('No field named "%s". Available: "%s".', $name, implode(', ', array_keys($this->types))));
        }

        return $this->typeOptions[$name];
    }

    public function getIdentifier(Object $object)
    {
        return $this->tableName.'#'.$object->_pumRawGet('id');
    }

    public function writeValue(Object $object, $name, $value)
    {
        $this->getType($name)->writeValue($object, $value, $name, $this->typeOptions[$name]);
    }

    public function readValue(Object $object, $name)
    {
        return $this->getType($name)->readValue($object, $name, $this->typeOptions[$name]);
    }

    public function refreshRaw(Object $object, $columnName)
    {
        if (isset($this->fieldDependencies[$columnName])) {
            $name = $this->fieldDependencies[$columnName];
            if ($object->_pumHasFieldLoaded($name)) {
                $this->writeValue($object, $name, $object->get($name));
            }
        }
    }
}
