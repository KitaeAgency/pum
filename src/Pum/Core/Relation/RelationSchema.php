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
        $this->relations = new ArrayCollection();

        if (!is_null($this->getBeam())) {
            foreach($this->getBeam()->getObjects() as $object) {
                foreach($object->getFields() as $field) {
                    if ($field->getType() == self::RELATION_TYPE) {
                        $typeOptions = $field->getTypeOptions();

                        $fromName = $field->getLowercaseName();
                        $fromObject = $object;
                        $fromType = $typeOptions['type'];

                        $toBeam = $this->objectFactory->getBeam(isset($typeOptions['target_beam']) ? $typeOptions['target_beam'] : $this->getBeam()->getName());

                        try {
                            $toObject = $toBeam->getObject($typeOptions['target']);
                        } catch (DefinitionNotFoundException $e) {
                            continue;
                        }
                        if (isset($typeOptions['inversed_by'])) {
                            $toName = Namer::toLowercase($typeOptions['inversed_by']);
                        } else {
                            $toName = null;
                        }

                        $relation = new Relation($fromName, $fromObject, $fromType, $toName, $toObject);
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
        // New relations data
        $dataRelations = array();
        foreach ($this->relations as $relation) {
            // Relation
            $fieldName   = Namer::toLowercase($relation->getFromName());
            $target      = $relation->getToObject()->getName();
            $target_beam = $relation->getToObject()->getBeam()->getName();
            $type        = $relation->getFromType();

            //Inverse Relation
            $inverseFieldName   = Namer::toLowercase($relation->getToName());
            $inverseTarget      = $relation->getFromObject()->getName();
            $inverseTarget_beam = $relation->getFromObject()->getBeam()->getName();

            // Relations data
            $dataRelations[md5($inverseTarget_beam.$inverseTarget.$fieldName)] = array(
                'object'      => $relation->getFromObject(),
                'fieldName'   => $fieldName,
                'typeOptions' => array(
                    'target'      => $target,
                    'target_beam' => $target_beam,
                    'inversed_by' => $inverseFieldName,
                    'type'        => $type,
                    'is_external' => $relation->isExternal(),
                    'owning'      => true,
                )
            );

            // Inverse relations data
            if (!is_null($inverseFieldName)) {
                $dataRelations[md5($target_beam.$target.$inverseFieldName)] = array(
                    'object'      => $relation->getToObject(),
                    'fieldName'   => $inverseFieldName,
                    'typeOptions' => array(
                        'target'      => $inverseTarget,
                        'target_beam' => $inverseTarget_beam,
                        'inversed_by' => $fieldName,
                        'type'        => Relation::getInverseType($type),
                        'is_external' => $relation->isExternal(),
                        'owning'      => false,
                    )
                );
            }
        }

        // Merging existing relations with new ones
        foreach($this->getBeam()->getObjects() as $object) {
            foreach($object->getFields() as $field) {
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
     * @return boolean
     */
    private function isExistedInverseRelation(Relation $relation)
    {
        foreach ($this->relations as $rel) {
            if($relation->getFromName() == $rel->getToName()
                && $relation->getFromObject()->getBeam()->getName() == $rel->getToObject()->getBeam()->getName()
                  && $relation->getFromObject()->getName() == $rel->getToObject()->getName()) {
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
            foreach ($beam->getProjects() as $project) {
                $project->raise(Events::PROJECT_UPDATE, new ProjectEvent($project));
            }
            $this->objectFactory->saveBeam($beam);
        }
    }
}
