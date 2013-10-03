<?php

namespace Pum\Core\Relation;

use Pum\Core\Definition\Beam;
use Pum\Core\ObjectFactory;
use Doctrine\Common\Collections\ArrayCollection;

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
        $this->relations = new ArrayCollection();

        if (!is_null($this->getBeam())) {
            foreach($this->getBeam()->getObjects() as $object) {
                foreach($object->getFields() as $field) {
                    if ($field->getType() == self::RELATION_TYPE) {
                        $typeOptions = $field->getTypeOptions();

                        $fromName = $field->getName();
                        $fromObject = $object;
                        $fromType = $typeOptions['type'];

                        $toBeam = $this->objectFactory->getBeam($typeOptions['target_beam']);
                        $toObject = $toBeam->getObject($typeOptions['target']);
                        $toType = Relation::getInverseType($fromType);
                        if (isset($typeOptions['inversed_by'])) {
                            $toName = $typeOptions['inversed_by'];
                        } else {
                            $toName = null;
                        }

                        $relation = new Relation($fromName, $fromObject, $fromType, $toName, $toObject, $toType);
                        if (!$this->isExistedInverseRelation($relation)) {
                            $this->addRelation($relation);
                        }
                    }
                }
            }

            foreach ($this->relations as $relation) {
                $relation->normalizeRelation();
            }
        }
    }

    /**
     * Inject relations into Beam
     */
    public function saveRelationsFromSchema()
    {
        $this->removeRelationsFromBeam();

        $dataRelations = array();
        foreach ($this->relations as $relation) {
            // Relation
            $fieldName   = $relation->getFromName();
            $target      = $relation->getToObject()->getName();
            $target_beam = $relation->getToObject()->getBeam()->getName();
            $type        = $relation->getFromType();

            //Inverse Relation
            $inverseFieldName   = $relation->getToName();
            $inverseTarget      = $relation->getFromObject()->getName();
            $inverseTarget_beam = $relation->getFromObject()->getBeam()->getName();
            $inverseType        = $relation->getToType();

            // Relation data
            if ($relation->getFromObject()->hasField($fieldName)) {
                throw new \RuntimeException(sprintf('Field "%s" is already present in object "%s".', $fieldName, $relation->getFromObject()->getName()));
            }

            $dataRelations[] = array(
                'object'      => $relation->getFromObject(),
                'fieldName'   => $fieldName,
                'typeOptions' => array(
                    'target'      => $target,
                    'target_beam' => $target_beam,
                    'inversed_by' => $inverseFieldName,
                    'type'        => $type,
                    'is_external' => $relation->isExternal()
                )
            );

            // Inverse relation data
            if (!is_null($inverseFieldName)) {
                if ($relation->getToObject()->hasField($inverseFieldName)) {
                    throw new \RuntimeException(sprintf('Field "%s" is already present in object "%s".', $inverseFieldName, $relation->getToObject()->getName()));
                }

                $dataRelations[] = array(
                    'object'      => $relation->getToObject(),
                    'fieldName'   => $inverseFieldName,
                    'typeOptions' => array(
                        'target'      => $inverseTarget,
                        'target_beam' => $inverseTarget_beam,
                        'inversed_by' => $fieldName,
                        'type'        => $inverseType,
                        'is_external' => $relation->isExternal()
                    )
                );
            }
        }

        // At this point, we can safety delete relations before inserting new ones
        $this->saveBeams();

        // Inserting new relations
        foreach ($dataRelations as $dataRelation) {
            $object      = $dataRelation['object'];
            $fieldName   = $dataRelation['fieldName'];
            $typeOptions = $dataRelation['typeOptions'];

            $object->createField($fieldName, self::RELATION_TYPE, $typeOptions);
        }

        // Saving new relations
        $this->saveBeams();
    }

    /**
     * Remove relations from Beam
     */
    private function removeRelationsFromBeam()
    {
        foreach($this->getBeam()->getObjects() as $object) {
            foreach($object->getFields() as $field) {
                if ($field->getType() == self::RELATION_TYPE) {
                    //Remove orginal relation
                    $object->removeField($field);

                    //Remove original reverse relation
                    $typeOptions = $field->getTypeOptions();
                    $toBeam      = $this->objectFactory->getBeam($typeOptions['target_beam']);
                    $toObject    = $toBeam->getObject($typeOptions['target']);
                    $toName      = $typeOptions['inversed_by'];
                    if ($toObject->hasField($toName)) {
                        $toObject->removeField($toObject->getField($toName));
                    }
                }
            }
        }
    }

    /**
     * @return boolean
     */
    private function isExistedInverseRelation(Relation $relation)
    {
        foreach ($this->relations as $rel) {
            if($relation->getFromName() == $rel->getToName()
                && $relation->getFromObject()->getBeam()->getName() == $rel->getToObject()->getBeam()->getName()
                  && $relation->getFromObject()->getName() == $rel->getToObject()->getName()
                    && $relation->getFromType() == $rel->getToType()) {
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
    }
}
