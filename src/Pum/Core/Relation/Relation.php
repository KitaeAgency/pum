<?php

namespace Pum\Core\Relation;

use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Exception\UnResolvedRelationException;
use Pum\Core\Schema\SchemaInterface;

/**
 * A Relation.
 */
class Relation
{
    const ONE_TO_MANY      = 'one-to-many';
    const MANY_TO_ONE      = 'many-to-one';
    const MANY_TO_MANY     = 'many-to-many';
    const ONE_TO_ONE       = 'one-to-one';
    const IMPORT_RENAME    = 'rename';
    const IMPORT_OVERWRITE = 'overwrite';
    const IMPORT_IGNORE    = 'ignore';

    /**
     * @var string
     */
    protected $fromName;

    /**
     * @var ObjectDefinition
     */
    protected $fromObject;

    /**
     * @var string
     */
    protected $fromType;

    /**
     * @var string
     */
    protected $toName;

    /**
     * @var string
     */
    protected $targetName;

    /**
     * @var string
     */
    protected $toBeamName;

    /**
     * @var ObjectDefinition
     */
    protected $toObject;

    /**
     * @var boolean
     */
    protected $isSleeping;

    /**
     * @var boolean
     */
    protected $owning;

    /**
     * @var boolean
     */
    protected $required;

    /**
     * @var string
     */
    protected $indexBy;

    /**
     * @var string
     */
    protected $cascade;

    /**
     * @var boolean
     */
    private $resolved;

    /**
     * Constructor.
     */
    public function __construct(
        $fromName = null,
        ObjectDefinition $fromObject = null,
        $fromType = null,
        $toName = null,
        $targetName = null,
        $toBeamName = null,
        $options = array()
    ) {
        //TODO add resolvedRelation class with to object
        $this->fromName = $fromName;
        $this->fromObject = $fromObject;
        $this->fromType = $fromType;

        $this->toName = $toName;
        $this->targetName = $targetName;
        $this->toBeamName = $toBeamName;

        $this->resolved = false;

        $this->resolveOptions($options);

    }

    public function resolveOptions($options)
    {
        $this->isSleeping = isset($options['is_sleeping']) ? $options['is_sleeping'] : false;
        $this->required   = isset($options['required']) ? $options['required'] : false;
        $this->indexBy   = isset($options['index_by']) ? $options['index_by'] : null;
        $this->cascade   = isset($options['cascade']) ? $options['cascade'] : null;

        if ($this->fromType == self::ONE_TO_MANY) {
            $this->owning = false;
        } elseif ($this->fromType == self::MANY_TO_ONE) {
            $this->owning = true;
        }

        if (null === $this->owning) {
            $this->owning = isset($options['owning']) ? $options['owning'] : true;
        }
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param $fromName
     * @return $this
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * @return ObjectDefinition
     */
    public function getFromObject()
    {
        return $this->fromObject;
    }

    /**
     * @param $fromObject
     * @return $this
     */
    public function setFromObject($fromObject)
    {
        $this->fromObject = $fromObject;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromType()
    {
        return $this->fromType;
    }

    /**
     * @param $fromType
     * @return $this
     * @throws \RuntimeException
     */
    public function setFromType($fromType)
    {
        if (!in_array($fromType, $this->getTypes())) {
            throw new \RuntimeException(
                sprintf(
                    'Unvalid type "%s". Authorized types are : "%s".',
                    $fromType,
                    implode(',', $this->getTypes())
                )
            );
        }
        $this->fromType = $fromType;
        return $this;
    }

    /**
     * @return string
     */
    public function getToName()
    {
        return $this->toName;
    }

    /**
     *
     * @param $toName
     * @return $this
     */
    public function setToName($toName)
    {
        $this->toName = $toName;

        return $this;
    }

    /**
     * @return ObjectDefinition
     * @throws \Pum\Core\Exception\UnResolvedRelationException
     */
    public function getToObject()
    {
        return $this->toObject;
    }

    /**
     * @param $toObject
     * @return $this
     */
    public function setToObject($toObject)
    {
        $this->toObject = $toObject;
        $this->toBeamName = $toObject->getBeam()->getName();
        $this->resolved = true;
        return $this;
    }

    /**
     * @param string $targetName
     * @return $this
     */
    public function setTargetName($targetName)
    {
        $this->targetName = $targetName;

        return $this;
    }

    /**
     * @return string
     */
    public function getTargetName()
    {
        return $this->targetName;
    }

    /**
     * @param string $toBeamName
     * @return $this
     */
    public function setToBeamName($toBeamName)
    {
        $this->toBeamName = $toBeamName;

        return $this;
    }

    /**
     * @return string
     */
    public function getToBeamName()
    {
        return $this->toBeamName;
    }

    /**
     * @return boolean
     */
    public function isExternal()
    {
        return $this->getFromObject()->getBeam()->getName() != $this->getToBeamName();
    }

    /**
     * @param boolean $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return (bool)$this->required;
    }

    /**
     * @param boolean $owning
     */
    public function setOwning($owning)
    {
        $this->owning = $owning;
    }

    /**
     * @return boolean
     */
    public function isOwning()
    {
        return (bool)$this->owning;
    }

    /**
     * @return boolean
     */
    public function getReverseOwning()
    {
        return !$this->isOwning();
    }

    /**
     * @param boolean $isSleeping
     */
    public function setIsSleeping($isSleeping)
    {
        $this->isSleeping = $isSleeping;
    }

    /**
     * @return boolean
     */
    public function isSleeping()
    {
        return (bool)$this->isSleeping;
    }

    /**
     * @param string
     */
    public function setIndexBy($indexBy)
    {
        $this->indexBy = $indexBy;
    }

    /**
     * @return string
     */
    public function getIndexBy()
    {
        return $this->indexBy;
    }

    /**
     * @param string
     */
    public function setCascade($cascade)
    {
        $this->cascade = $cascade;
    }

    /**
     * @return string
     */
    public function getCascade()
    {
        return $this->cascade;
    }    

    /**
     * @return Relation
     */
    public function normalizeRelation()
    {
        if (!is_null($this->toName) && $this->fromType == self::MANY_TO_ONE && !$this->isExternal()) {
            $tmp            = $this->fromName;
            $this->fromName = $this->toName;
            $this->toName   = $tmp;

            $tmp              = $this->fromObject;
            $this->fromObject = $this->toObject;
            $this->toObject   = $tmp;

            $tmp            = $this->fromType;
            $this->fromType = self::ONE_TO_MANY;

            $this->owning = $this->getReverseOwning();
        }
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return array(self::ONE_TO_MANY, self::MANY_TO_ONE, self::MANY_TO_MANY, self::ONE_TO_ONE);
    }

    /**
     * @param $type
     * @return null
     */
    public static function getInverseType($type)
    {
        $inverseTypes = array(
            self::ONE_TO_MANY  => self::MANY_TO_ONE,
            self::MANY_TO_ONE  => self::ONE_TO_MANY,
            self::MANY_TO_MANY => self::MANY_TO_MANY,
            self::ONE_TO_ONE   => self::ONE_TO_ONE
        );

        return (isset($inverseTypes[$type])) ? $inverseTypes[$type] : null;
    }

    /**
     * @param SchemaInterface $schema
     */
    public function resolve(SchemaInterface $schema)
    {
        if (!$this->resolved) {
            $this->toObject = $schema->getBeam($this->toBeamName)->getObject($this->targetName);
            $this->resolved = true;
        }
    }
}
