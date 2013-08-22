<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Metadata;


use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Definition\Relation;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Extension\EmFactory\Doctrine\Reflection\ObjectReflectionClass;
use Pum\Core\Extension\EmFactory\EmFactoryExtension;
use Pum\Core\SchemaManager;

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

    public function loadPum()
    {
        $class    = $this->getName();
        $metadata = $class::_pumGetMetadata();

        // An ID for all
        $this->mapField(array(
            'fieldName' => 'id',
            'type'      => 'integer',
        ));
        $this->setIdentifier(array('id'));
        $this->setIdGeneratorType(self::GENERATOR_TYPE_AUTO);

        // Tablename
        $this->setTableName($metadata->tableName);

        foreach ($metadata->types as $name => $type) {
            $metadata->getType($name)->mapDoctrineFields($this, $name, $metadata->typeOptions[$name]);
        }

        // Relations
        foreach ($metadata->relations as $relation) {
            try {
                $this->mapRelation($relation);
            } catch (DefinitionNotFoundException $e) {}
        }
    }

    public function mapRelation(array $relation)
    {
        switch ($relation['type']) {
            case Relation::ONE_TO_MANY:
                if (null === $relation['toName']) {
                    # http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html#one-to-many-unidirectional-with-join-table
                    $this->mapManyToMany(array(
                        'fieldName'    => $relation['fromName'],
                        'targetEntity' => $relation['toClass'],
                        'joinTable' => array(
                            'name'   => $relation['tableName'],
                            'joinColumns' => array(array('name' => $relation['from'].'_id', 'referencedColumnName' => 'id')),
                            'inverseJoinColumns' => array(array('name' => $relation['to'].'_id', 'referencedColumnName' => 'id', 'unique' => true)),
                        )
                    ));
                } else {
                    $this->mapOneToMany(array(
                        'fieldName'    => $relation['fromName'],
                        'targetEntity' => $relation['toClass'],
                        'mappedBy'    => $relation['toName'],
                    ));
                }

                break;

            case Relation::MANY_TO_ONE:
                $this->mapManyToOne(array(
                    'fieldName'    => $relation['fromName'],
                    'targetEntity' => $relation['toClass'],
                    'joinColumns' => array(
                        array('name' => $relation['fromName'].'_id', 'referencedColumnName' => 'id')
                    )
                ));

                break;
        }
    }

    public function getAdditionalTables()
    {
        $result = array();
        foreach ($this->getAssociationMappings() as $mapping) {
            if (isset($mapping['joinTable'])) {
                $result[] = $mapping['joinTable']['name'];
            }
        }

        return $result;
    }
}
