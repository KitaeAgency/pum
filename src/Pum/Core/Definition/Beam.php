<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Exception\DefinitionNotFoundException;

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
     * @var ArrayCollection
     */
    protected $relations;

    /**
     * @var ArrayCollection
     */
    protected $projects;

    /**
     * Constructor.
     */
    public function __construct($name = null)
    {
        $this->name      = $name;
        $this->objects   = new ArrayCollection();
        $this->relations = new ArrayCollection();
        $this->projects  = new ArrayCollection();
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
        $definition->setBeam($this);

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

    /**
     * @return Beam
     */
    public function addRelation(Relation $relation)
    {
        $this->getRelations()->add($relation);
        $relation->setBeam($this);

        return $this;
    }

    /**
     * @return Beam
     */
    public function removeRelation(Relation $relation)
    {
        $this->getRelations()->removeElement($relation);
    }

    /**
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @return ObjectDefinition
     *
     * @throws DefinitionNotFoundException
     */
    public function getDefinition($name)
    {
        foreach ($this->getObjects() as $object) {
            if ($object->getName() === $name) {
                return $object;
            }
        }

        throw new DefinitionNotFoundException($name);
    }
}
