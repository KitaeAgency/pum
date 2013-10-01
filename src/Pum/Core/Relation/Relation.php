<?php

namespace Pum\Core\Relation;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;

/**
 * A Relation.
 */
class Relation
{
    const ONE_TO_MANY  = 'one-to-many';
    const MANY_TO_ONE  = 'many-to-one';
    const MANY_TO_MANY = 'many-to-many';
    const ONE_TO_ONE   = 'one-to-one';

    /**
     * @var string
     */
    protected $fromName;

    /**
     * @var Beam
     */
    protected $fromBeam;

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
     * @var Beam
     */
    protected $toBeam;

    /**
     * @var ObjectDefinition
     */
    protected $toObject;

    /**
     * @var string
     */
    protected $toType;

    /**
     * Constructor.
     */
    public function __construct($fromName = null, Beam $fromBeam = null, ObjectDefinition $fromObject = null, $fromType = null, 
        $toName = null, Beam $toBeam = null, ObjectDefinition $toObject = null, $toType = null)
    {
        $this->fromName   = $fromName;
        $this->fromBeam   = $fromBeam;
        $this->fromObject = $fromObject;
        $this->fromType   = $fromType;

        $this->toName   = $toName;
        $this->toBeam   = $toBeam;
        $this->toObject = $toObject;
        $this->toType   = $toType;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @return Relation
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromBeam()
    {
        return $this->fromBeam;
    }

    /**
     * @return Relation
     */
    public function setFromBeam($fromBeam)
    {
        $this->fromBeam = $fromBeam;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromObject()
    {
        return $this->fromObject;
    }

    /**
     * @return Relation
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
     * @return Relation
     */
    public function setFromType($fromType)
    {
        if (!in_array($fromType, $this->getTypes())) {
            throw new \RuntimeException(sprintf('Unvalid type "%s". Authorized types are : "%s".', $fromType, implode(',', $this->getTypes())));
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
     * @return Relation
     */
    public function setToName($toName)
    {
        $this->toName = $toName;

        return $this;
    }

    /**
     * @return string
     */
    public function getToBeam()
    {
        return $this->toBeam;
    }

    /**
     * @return Relation
     */
    public function setToBeamName($toBeam)
    {
        $this->toBeamName = $toBeam;

        return $this;
    }

    /**
     * @return string
     */
    public function getToObject()
    {
        return $this->toObject;
    }

    /**
     * @return Relation
     */
    public function setToObject($toObject)
    {
        $this->toObject = $toObject;

        return $this;
    }

    /**
     * @return string
     */
    public function getToType()
    {
        return $this->toType;
    }

    /**
     * @return Relation
     */
    public function setToType($toType)
    {
        if (!in_array($toType, $this->getTypes())) {
            throw new \RuntimeException(sprintf('Unvalid type "%s". Authorized types are : "%s".', $toType, implode(',', $this->getTypes())));
        }

        $this->toType = $toType;

        return $this;
    }


    /**
     * @return boolean
     */
    public function isExternal()
    {
        return $this->getFromBeamName() == $this->getToBeamName();
    }

    /**
     * @return Relation
     */
    public function normalizeRelation()
    {
        if ($this->fromType == self::MANY_TO_ONE && $this->fromBeam->getName() == $this->toBeam->getName()) {
            $tmp            = $this->fromName;
            $this->fromName = $this->toName;
            $this->toName   = $tmp;

            $tmp            = $this->fromBeam;
            $this->fromBeam = $this->toBeam;
            $this->toBeam   = $tmp;

            $tmp              = $this->fromObject;
            $this->fromObject = $this->toObject;
            $this->toObject   = $tmp;

            $tmp            = $this->fromType;
            $this->fromType = $this->toType;
            $this->toType   = $tmp;
        }
    }

    /**
     * @return boolean
     */
    public static function getTypes()
    {
        return array(self::ONE_TO_MANY, self::MANY_TO_ONE, self::MANY_TO_MANY, self::ONE_TO_ONE);
    }

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
}