<?php

namespace Pum\Core\Definition;

class Relation
{
    const ONE_TO_MANY  = 'one-to-many';
    const MANY_TO_ONE  = 'many-to-one';
    const MANY_TO_MANY = 'many-to-many';

    protected $id;
    protected $beam;
    protected $from;
    protected $fromName;
    protected $to;
    protected $toName;
    protected $type;

    /**
     * @param string $from     origin entity object name
     * @param string $fromName name of the relation on the origin entity
     * @param string $to       target entity object name
     * @param string $toName   name of the relation on the target entity (null = no relation)
     * @param string $type     type of relation (see self::* constants)
     */
    public function __construct($from, $fromName, $to, $toName = null, $type = self::MANY_TO_ONE)
    {
        $this->from     = $from;
        $this->fromName = $fromName;
        $this->to       = $to;
        $this->toName   = $toName;
        $this->type     = $type;
    }

    /**
     * @see self::__construct
     *
     * @return Relation
     */
    static public function create($from, $fromName, $to, $toName = null, $type = self::MANY_TO_ONE)
    {
        return new self($from, $fromName, $to, $toName, $type);
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Beam
     */
    public function getBeam()
    {
        return $this->beam;
    }

    /**
     * @return Relation
     */
    public function setBeam(Beam $beam)
    {
        $this->beam = $beam;

        return $this;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return Relation
     */
    public function setFrom($from)
    {
        $this->from = $from;
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
    public function getTo()
    {
        return $this->from;
    }

    /**
     * @return Relation
     */
    public function setTo($from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getToName()
    {
        return $this->fromName;
    }

    /**
     * @return Relation
     */
    public function setToName($fromName)
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * @return string see self:: constants
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Relation
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
