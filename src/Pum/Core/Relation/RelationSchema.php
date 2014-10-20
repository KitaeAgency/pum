<?php

namespace Pum\Core\Relation;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Event\ProjectEvent;
use Pum\Core\Events;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Extension\Util\Namer;
use Pum\Core\ObjectFactory;

/**
 * A RelationSchema.
 */
class RelationSchema
{
    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var Beam
     */
    protected $beam;

    /**
     * @var ObjectDefinition
     */
    protected $objectDefinition;

    /**
     * @var ArrayCollection
     */
    protected $relations;

    /**
     * Constructor.
     */
    public function __construct(ObjectFactory $objectFactory, Beam $beam, ObjectDefinition $objectDefinition = null)
    {
        $this->objectFactory    = $objectFactory;
        $this->beam             = $beam;
        $this->objectDefinition = $objectDefinition;

        $this->createRelations();

    }

    /**
     * @return Beam
     */
    public function getBeam()
    {
        return $this->beam;
    }

    /**
     * @return RelationSchema
     */
    public function setBeam(Beam $beam)
    {
        $this->beam = $beam;

        return $this;
    }

    /**
     * @return ObjectDefinition
     */
    public function getObjectDefinition()
    {
        return $this->objectDefinition;
    }

    /**
     * @return RelationSchema
     */
    public function setObjectDefinition(ObjectDefinition $objectDefinition)
    {
        $this->objectDefinition = $objectDefinition;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @return RelationSchema
     */
    public function addRelation(Relation $relation)
    {
        $this->relations->add($relation);

        return $this;
    }

    /**
     * @return RelationSchema
     */
    public function removeRelation(Relation $relation)
    {
        $this->relations->removeElement($relation);

        return $this;
    }

    /**
     * Import relations from Beam
     */
    private function createRelations()
    {
        $this->relations = new ArrayCollection();

        switch (true) {
            case !is_null($this->getObjectDefinition()):
                $this->relations = new ArrayCollection($this->getObjectDefinition()->getRelations());
                break;

            case !is_null($this->getBeam()):
                $this->relations = new ArrayCollection($this->getBeam()->getRelations());
                break;
        }

        $normalizeRelation = $this->getObjectDefinition() ? false : true;

        foreach ($this->relations as $relation) {
            $relation->resolve($this->objectFactory->getSchema());

            if ($normalizeRelation) {
                $relation->normalizeRelation();
            }
        }
    }

    /**
     * Inject relations into Schema
     */
    public function flush()
    {
        // New relations data
        $dataRelations = array();

        foreach ($this->relations as $relation) {
            // Relation
            $fieldName        = Namer::toLowercase($relation->getFromName());
            $target           = $relation->getToObject()->getName();
            $target_beam      = $relation->getToObject()->getBeam()->getName();
            $target_beam_seed = $relation->getToObject()->getBeam()->getSeed();
            $type             = $relation->getFromType();

            // Autofill new relation
            if (null === $relation->getFromObject()) {
                $relation->setFromObject($this->getObjectDefinition());
            }

            // Inverse relation
            $inverseFieldName        = Namer::toLowercase($relation->getToName());
            $inverseTarget           = $relation->getFromObject()->getName();
            $inverseTarget_beam_seed = $relation->getFromObject()->getBeam()->getSeed();
            $inverseTarget_beam      = $relation->getFromObject()->getBeam()->getName();

            // Relations data
            $dataRelations[md5($inverseTarget_beam.$inverseTarget.$fieldName)] = array(
                'object'      => $relation->getFromObject(),
                'fieldName'   => $fieldName,
                'typeOptions' => array(
                    'inversed_by'      => $inverseFieldName,
                    'is_external'      => $relation->isExternal(),
                    'target'           => $target,
                    'target_beam'      => $target_beam,
                    'target_beam_seed' => $target_beam_seed,
                    'type'             => $type,
                    'owning'           => $relation->isOwning(),
                    'is_sleeping'      => $relation->isSleeping(),
                    'required'         => $relation->isRequired()
                )
            );

            // Inverse relations data
            if (!is_null($inverseFieldName)) {
                $dataRelations[md5($target_beam.$target.$inverseFieldName)] = array(
                    'object'      => $relation->getToObject(),
                    'fieldName'   => $inverseFieldName,
                    'typeOptions' => array(
                        'inversed_by'      => $fieldName,
                        'is_external'      => $relation->isExternal(),
                        'target'           => $inverseTarget,
                        'target_beam'      => $inverseTarget_beam,
                        'target_beam_seed' => $inverseTarget_beam_seed,
                        'type'             => Relation::getInverseType($type),
                        'owning'           => $relation->getReverseOwning(),
                        'is_sleeping'      => $relation->isSleeping(),
                        'required'         => $relation->isRequired()
                    )
                );
            }
        }

        // Merging existing relations with new ones
        $this->relations = (null !== $this->getObjectDefinition()) ? new ArrayCollection($this->getObjectDefinition()->getRelations()) : new ArrayCollection($this->getBeam()->getRelations());
        $objects         = (null !== $this->getObjectDefinition()) ? array($this->getObjectDefinition()) : $this->getBeam()->getObjects();

        foreach ($objects as $object) {
            foreach ($object->getFields() as $field) {
                if ($field->getType() == FieldDefinition::RELATION_TYPE) {
                    $key = md5($this->getBeam()->getName().$object->getName().$field->getName());

                    if (isset($dataRelations[$key])) {
                        $field->setTypeOptions($dataRelations[$key]['typeOptions']);
                        unset($dataRelations[$key]);
                    } else {
                        $object->removeField($field);
                    }

                    $typeOptions = $field->getTypeOptions();
                    $toBeam      = $this->objectFactory->getBeam($typeOptions['target_beam']);
                    $toObject    = $toBeam->getObject($typeOptions['target']);
                    $toName      = $typeOptions['inversed_by'];

                    if ($toName && $toObject->hasField($toName)) {
                        if ((null !== $this->getObjectDefinition() && $object !== $toObject) || $field->getTypeOption('is_external')) {
                            $inverseKey = md5($toBeam->getName().$toObject->getName().$toName);

                            if (isset($dataRelations[$inverseKey])) {
                                $toObject->getField($toName)->setTypeOptions($dataRelations[$inverseKey]['typeOptions']);
                                unset($dataRelations[$inverseKey]);
                            } else {
                                $toObject->removeField($toObject->getField($toName));
                            }
                        }
                    }
                }
            }
        }

        // Inserting new relations left
        foreach ($dataRelations as $dataRelation) {
            $object      = $dataRelation['object'];
            $fieldName   = $dataRelation['fieldName'];
            $typeOptions = $dataRelation['typeOptions'];

            if (!$object->hasField($fieldName)) {
                $object->createField($fieldName, FieldDefinition::RELATION_TYPE, $typeOptions);
            } elseif ($object->getField($fieldName)->getType() != FieldDefinition::RELATION_TYPE) {
                throw new \RuntimeException(sprintf('Field "%s" is already present in object "%s".', $fieldName, $object->getName()));
            } else {
                $object->getField($fieldName)->setTypeOptions($typeOptions);
            }
        }

        // Saving new relations
        $this->saveBeams();
    }

    /**
     * Find out if an existing inverted relation already exist in relation set
     *
     * @param array $relations
     * @param Relation $relation
     * @return bool
     */
    public static function isExistedInverseRelation(array $relations, Relation $relation)
    {
        foreach ($relations as $rel) {
            if ($relation->getFromName() == $rel->getToName()
                && $relation->getFromObject()->getBeam()->getName() == $rel->getToBeamName()
                && $relation->getFromObject()->getName() == $rel->getTargetName()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array Beam
     */
    private function getBeamsName()
    {
        $beams[]     = $this->getBeam();
        $beamsName[] = $this->getBeam()->getName();

        foreach ($this->relations as $relation) {
            $relation->resolve($this->objectFactory->getSchema());
            if (!in_array($relation->getFromObject()->getBeam()->getName(), $beamsName)) {
                $beamsName[] = $relation->getFromObject()->getBeam()->getName();
                $beams    [] = $relation->getFromObject()->getBeam();
            }

            if (!in_array($relation->getToObject()->getBeam()->getName(), $beamsName)) {
                $beamsName[] = $relation->getToObject()->getBeam()->getName();
                $beams    [] = $relation->getToObject()->getBeam();
            }
        }

        return $beams;
    }

    /**
     * Save Beams
     */
    private function saveBeams()
    {
        foreach ($this->getBeamsName() as $beam) {
            $this->objectFactory->saveBeam($beam);
        }

        foreach ($this->getBeamsName() as $beam) {
            foreach ($beam->getProjects() as $project) {
                $project->raise(Events::PROJECT_SCHEMA_UPDATE, new ProjectEvent($project));
            }
        }
    }
}
