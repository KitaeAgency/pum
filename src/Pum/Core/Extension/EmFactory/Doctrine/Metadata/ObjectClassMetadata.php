<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Metadata;


use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Reflection\ObjectReflectionClass;
use Pum\Core\SchemaManager;

/**
 * Extend default class metadata to allow loading from
 * a PUM object definition.
 */
class ObjectClassMetadata extends ClassMetadata
{
    protected $schemaManager;

    public function __construct(SchemaManager $schemaManager, $entityName)
    {
        parent::__construct($entityName);

        $this->schemaManager = $schemaManager;
        $this->reflClass = new ObjectReflectionClass($entityName);

    }

    public function loadFromObjectDefinition($projectName, ObjectDefinition $definition)
    {
        // An ID for all
        $this->mapField(array(
            'fieldName' => 'id',
            'type'      => 'integer',
        ));
        $this->setIdentifier(array('id'));
        $this->setIdGeneratorType(self::GENERATOR_TYPE_AUTO);

        // Tablename
        $this->setTableName('object_'.$this->safeValue($projectName.'__'.$definition->getName()));

        foreach ($definition->getFields() as $field) {
            $this->schemaManager->getType($field->getType())->mapDoctrineFields($this, $field);
        }
    }

    private function safeValue($text)
    {
        return preg_replace('/[^a-z0-9]/i', '_', $text);
    }
}
