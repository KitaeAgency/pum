<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * A beam.
 */
class Beam
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ArrayCollection
     */
    protected $objects;

    /**
     * Constructor.
     */
    public function __construct($name = null)
    {
        $this->name     = $name;
        $this->objects  = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Object
     */
    public static function create($name = null)
    {
        return new self($name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Object
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Beam
     */
    public function addObject(ObjectDefinition $definition)
    {
        $this->getObjects()->add($definition);

        return $this;
    }

    /**
     * @return Beam
     */
    public function removeObject(ObjectDefinition $definition)
    {
        $this->getObjects()->removeElement($definition);
    }

    /**
     * @return array
     */
    public function getObjects()
    {
        return $this->objects;
    }
}
