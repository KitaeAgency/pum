<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Pum\Core\Event\FieldDefinitionEvent;
use Pum\Core\Events;
use Pum\Core\Extension\Util\Namer;

/**
 * Definition of a dynamic object.
 */
class FieldDefinition extends EventObject
{
    const RELATION_TYPE = 'relation';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var ObjectDefinition
     */
    protected $object;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $typeOptions;

    /**
     * Constructor.
     */
    public function __construct($name = null, $type = null, $typeOptions = array())
    {
        $this->name        = $name;
        $this->type        = $type;
        $this->typeOptions = $typeOptions;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array $typeOptions
     * @return FieldDefinition
     */
    public static function create($name = null, $type = null, array $typeOptions = array())
    {
        return new self($name, $type, $typeOptions);
    }

    /**
     * @return Object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Changes associated object.
     *
     * @param ObjectDefinition $object
     * @return $this
     */
    public function setObject(ObjectDefinition $object = null)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns camel case name of the field.
     *
     * @return string
     */
    public function getCamelCaseName()
    {
        return Namer::toCamelCase($this->name);
    }
    /**
     * Returns lower case name of the field.
     *
     * @return string
     */
    public function getLowercaseName()
    {
        return Namer::toLowercase($this->name);
    }

    /**
     * @param $name
     * @return FieldDefinition
     */
    public function setName($name)
    {
        if ($this->name && $name !== $this->name) {
            $this->raise(Events::OBJECT_DEFINITION_FIELD_UPDATED, new FieldDefinitionEvent($this));
        }

        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getTypeOptions()
    {
        return $this->typeOptions;
    }

    /**
     * @param $name
     * @param $default
     * @return array
     */
    public function getTypeOption($name, $default = null)
    {
        return isset($this->typeOptions[$name]) ? $this->typeOptions[$name] : $default;
    }

    /**
     * @param $name
     * @param $value
     * @return FieldDefinition
     */
    public function setTypeOption($name, $value)
    {
        $this->typeOptions[$name] = $value;

        return $this;
    }

    /**
     * @param array $typeOptions
     * @return FieldDefinition
     */
    public function setTypeOptions(array $typeOptions)
    {
        $this->typeOptions = $typeOptions;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     * @return FieldDefinition
     */
    public function setType($type)
    {
        if ($this->type && $type !== $this->type) {
            $this->raise(Events::OBJECT_DEFINITION_FIELD_UPDATED, new FieldDefinitionEvent($this));
        }

        $this->type = $type;

        return $this;
    }

    public function getMetadataDefinition()
    {
        return array(
            'fieldName' => $this->getName(),
            'type'      => $this->getType(),
        );
    }

    /**
     * Returns $this as an array
     */
    public function toArray()
    {
        return array(
            'name' => $this->getName(),
            'type' => $this->getType(),
            'typeOptions' => $this->getTypeOptions()
            );
    }

    /**
     * Create a field based on an array
     *
     * @param array $array
     * @return ObjectDefinition
     * @throws \InvalidArgumentException
     */
    public static function createFromArray(array $array)
    {
        if (!$array || !is_array($array)) {
            throw new \InvalidArgumentException('FieldDefinition - An array is excepted');
        }

        $attributes = array(
            'name' => 'string',
            'type' => 'string',
            'typeOptions' => 'array'
            );
        foreach ($attributes as $name => $type) {
            if (!isset($array[$name])) {
                throw new \InvalidArgumentException(sprintf('FieldDefinition - key "%s" is missing', $name));
            }
            $typeTest = "is_$type";
            if (!$typeTest($array[$name])) {
                throw new \InvalidArgumentException(sprintf('FieldDefinition - "%s" value must be %s', $name, $type));
            }
        }

        return self::create($array['name'], $array['type'], $array['typeOptions']);
    }
}
