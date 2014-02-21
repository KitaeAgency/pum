<?php

namespace Pum\Core\Relation;

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
     * @var ObjectDefinition
     */
    protected $toObject;

    /**
     * Constructor.
     */
    public function __construct($fromName = null, ObjectDefinition $fromObject = null, $fromType = null, $toName = null, ObjectDefinition $toObject = null)
    {
        $this->fromName   = $fromName;
        $this->fromObject = $fromObject;
        $this->fromType   = $fromType;

        $this->toName   = $toName;
        $this->toObject = $toObject;
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
     * @return boolean
     */
    public function isExternal()
    {
        return $this->getFromObject()->getBeam()->getName() != $this->getToObject()->getBeam()->getName();
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
