<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Metadata;


use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Definition\Relation;
use Pum\Core\Extension\EmFactory\Doctrine\Reflection\ObjectReflectionClass;
use Pum\Core\Extension\EmFactory\EmFactoryExtension;
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

    public function loadFromObjectDefinition(Project $project, ObjectDefinition $definition)
    {
        // An ID for all
        $this->mapField(array(
            'fieldName' => 'id',
            'type'      => 'integer',
        ));
        $this->setIdentifier(array('id'));
        $this->setIdGeneratorType(self::GENERATOR_TYPE_AUTO);

        // Tablename
        $this->setTableName('object_'.$this->safeValue($project->getName().'__'.$definition->getName()));

        // Fields
        foreach ($definition->getFields() as $field) {
            $this->schemaManager->getType($field->getType())->mapDoctrineFields($this, $field);
        }

        // Relations
        foreach ($project->getRelations() as $relation) {
            if ($relation->getFrom() === $definition->getName()) {
                $this->mapRelationFrom($project, $relation);
            } elseif ($relation->getTo() === $definition->getName()) {
                $this->mapRelationTo($project, $relation);
            }
        }
    }

    public function mapRelationFrom(Project $project, Relation $relation)
    {
        $projectManager = $this->schemaManager->getExtension(EmFactoryExtension::NAME)->getManager($project->getName());

        $toClass = $projectManager->getObjectClass($relation->getTo());

        switch ($relation->getType()) {
            case Relation::ONE_TO_MANY:
                if (!$relation->getToName()) {
                    # http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html#one-to-many-unidirectional-with-join-table
                    $this->mapManyToMany(array(
                        'fieldName'    => $relation->getFromName(),
                        'targetEntity' => $toClass,
                        'joinTable' => array(
                            'name'   => 'assoc__'.$this->safeValue($project->getName().'__'.$relation->getFrom().'_'.$relation->getFromName()),
                            'joinColumns' => array(array('name' => $relation->getFrom().'_id', 'referencedColumnName' => 'id')),
                            'inverseJoinColumns' => array(array('name' => $relation->getTo().'_id', 'referencedColumnName' => 'id', 'unique' => true)),
                        )
                    ));
                } else {
                    $this->mapOneToMany(array(
                        'fieldName'    => $relation->getFromName(),
                        'targetEntity' => $toClass,
                        'mappedBy'    => $relation->getToName(),
                    ));
                }

                break;

            case Relation::MANY_TO_ONE:
                $this->mapManyToOne(array(
                    'fieldName'    => $relation->getFromName(),
                    'targetEntity' => $toClass,
                    'joinColumns' => array(
                        array('name' => $relation->getFromName().'_id', 'referencedColumnName' => 'id')
                    )
                ));

                break;
        }
    }

    public function mapRelationTo(Project $project, Relation $relation)
    {
        if (!$relation->getToName()) {
            return;
        }

        switch ($relation->getType()) {
            case Relation::ONE_TO_MANY:
                $this->mapManyToOne(array(
                    'fieldName'    => $relation->getToName(),
                    'targetEntity' => $this->schemaManager->getExtension(EmFactoryExtension::NAME)->getManager($project->getName())->getObjectClass($relation->getFrom()),
                    'inversedBy'   => $relation->getFromName()
                ));
        }
    }

    private function safeValue($text)
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '_', $text));
    }
}
