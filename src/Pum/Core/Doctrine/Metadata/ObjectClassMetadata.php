<?php

namespace Pum\Core\Doctrine\Metadata;

use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Doctrine\Reflection\ObjectReflectionClass;

/**
 * Extend default class metadata to allow loading from
 * a PUM object definition.
 */
class ObjectClassMetadata extends ClassMetadata
{
    public function __construct($entityName)
    {
        parent::__construct($entityName);

        $this->reflClass = new ObjectReflectionClass($entityName);

    }

    public function loadFromObjectDefinition(ObjectDefinition $definition)
    {
        $this->mapField(array(
            'fieldName' => 'id',
            'type'      => 'integer',
        ));

        $this->setTableName('object_'.$definition->getName());

        $this->setIdGeneratorType(self::GENERATOR_TYPE_AUTO);

        $this->setIdentifier(array('id'));

        foreach ($definition->getFields() as $field) {
            $this->mapField(array(
                'fieldName' => $field->getName(),
                'type'      => $field->getType(),
                'nullable'  => true,
            ));
        }
    }
}
