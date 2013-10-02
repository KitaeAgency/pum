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

    public function createRelationsFromSchema()
    {
        foreach($this->getBeam()->getObjects() as $object) {
            foreach($object->getFields() as $field) {
                if ($field->getType() == self::RELATION_TYPE) {
                    $object->removeField($field);
                }
            }
        }

        // TODO create Relations From Schema
        foreach ($this->relations as $relation) {
            // Relation
            $fieldName = $relation->getFromName();

            //Inverse Relation
        }
    }

    /**
     * @return boolean
     */
    private function isExistedInverseRelation(Relation $relation)
    {
        foreach ($this->relations as $rel) {
            if($relation->getFromName() == $rel->getToName()
                  && $relation->getFromObject()->getName() == $rel->getToObject()->getName()
                    && $relation->getFromType() == $rel->getToType()) {
                        return true;
            }
        }

        return false;
    }
}
