<?php

namespace Pum\Core\Relation;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Definition\Beam;
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
    const RELATION_TYPE = 'relation';

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var Beam
     */
    protected $beam;

    /**
     * @var ArrayCollection
     */
    protected $relations;

    /**
     * Constructor.
     */
    public function __construct(Beam $beam = null, ObjectFactory $objectFactory)
    {
        $this->beam = $beam;
        $this->objectFactory = $objectFactory;
        $this->createRelationsFromBeam();

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
    private function createRelationsFromBeam()
    {

        if (!is_null($this->getBeam())) {
            $this->relations = new ArrayCollection($this->getBeam()->getRelations());

            foreach ($this->relations as $relation) {
                $relation->resolve($this->objectFactory->getSchema());
                $relation->normalizeRelation();
            }
        }
    }

    /**
     * Inject relations into Beam
     */
    public function saveRelationsFromSchema()
    {
        // New relations data
        $dataRelations = array();
        foreach ($this->relations as $relation) {
            // Relation
            $fieldName = Namer::toLowercase($relation->getFromName());
            $target = $relation->getToObject()->getName();
            $target_beam = $relation->getToObject()->getBeam()->getName();
            $target_beam_seed = $relation->getToObject()->getBeam()->getSeed();
            $type = $relation->getFromType();

            //Inverse Relation
            $inverseFieldName = Namer::toLowercase($relation->getToName());
            $inverseTarget = $relation->getFromObject()->getName();
            $inverseTarget_beam_seed = $relation->getFromObject()->getBeam()->getSeed();
            $inverseTarget_beam = $relation->getFromObject()->getBeam()->getName();

            // Relations data
            $dataRelations[md5($inverseTarget_beam.$inverseTarget.$fieldName)] = array(
                'object'      => $relation->getFromObject(),
                'fieldName'   => $fieldName,
                'typeOptions' => array(
                    'inversed_by'           => $inverseFieldName,
                    'is_external'           => $relation->isExternal(),
                    'target'                => $target,
                    'target_beam'           => $target_beam,
                    'target_beam_seed'      => $target_beam_seed,
                    'type'                  => $type,
                    'owning'                => true,
                )
            );

            // Inverse relations data
            if (!is_null($inverseFieldName)) {
                $dataRelations[md5($target_beam.$target.$inverseFieldName)] = array(
                    'object'      => $relation->getToObject(),
                    'fieldName'   => $inverseFieldName,
                    'typeOptions' => array(
                        'inversed_by'           => $fieldName,
                        'is_external'           => $relation->isExternal(),
                        'target'                => $inverseTarget,
                        'target_beam'           => $inverseTarget_beam,
                        'target_beam_seed'      => $inverseTarget_beam_seed,
                        'type'                  => Relation::getInverseType($type),
                        'owning'                => false,
                    )
                );
            }
        }

        // Merging existing relations with new ones
        foreach ($this->getBeam()->getObjects() as $object) {
            foreach ($object->getFields() as $field) {
                if ($field->getType() == self::RELATION_TYPE) {
                    $key = md5($this->getBeam()->getName().$object->getName().$field->getName());
                    if (isset($dataRelations[$key])) {
                        $field->setTypeOptions($dataRelations[$key]['typeOptions']);
                        unset($dataRelations[$key]);
                    } else {
                        $object->removeField($field);
                    }

                    $typeOptions = $field->getTypeOptions();
                    if ($field->getTypeOption('is_external')) {
                        $toBeam      = $this->objectFactory->getBeam($typeOptions['target_beam']);
                        $toObject    = $toBeam->getObject($typeOptions['target']);
                        $toName      = $typeOptions['inversed_by'];
                        if ($toObject->hasField($toName)) {
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
                $object->createField($fieldName, self::RELATION_TYPE, $typeOptions);
            } elseif ($object->getField($fieldName)->getType() != self::RELATION_TYPE) {
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
            foreach ($beam->getProjects() as $project) {
                $project->raise(Events::PROJECT_SCHEMA_UPDATE, new ProjectEvent($project));
            }
            $this->objectFactory->saveBeam($beam);
        }
    }
}
