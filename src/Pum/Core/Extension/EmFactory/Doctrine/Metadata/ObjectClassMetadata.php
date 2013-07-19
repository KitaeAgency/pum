<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Metadata;


use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Reflection\ObjectReflectionClass;
use Pum\Core\Manager;

/**
 * Extend default class metadata to allow loading from
 * a PUM object definition.
 */
class ObjectClassMetadata extends ClassMetadata
{
    protected $manager;

    public function __construct(SchemaManager $manager, $entityName)
    {
        parent::__construct($entityName);

        $this->manager = $manager;
        $this->reflClass = new ObjectReflectionClass($entityName);

    }

    public function loadFromObjectDefinition(ObjectDefinition $definition)
    {
        // An ID for all
        $this->mapField(array(
            'fieldName' => 'id',
            'type'      => 'integer',
        ));
        $this->setIdentifier(array('id'));
        $this->setIdGeneratorType(self::GENERATOR_TYPE_AUTO);

        // Tablename
        $this->setTableName('object_'.$definition->getName());

        foreach ($definition->getFields() as $field) {
            $this->manager->getType($field->getType())->mapDoctrineFields($this, $field);
        }
    }
}
