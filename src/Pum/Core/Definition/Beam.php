<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Exception\RelationNotFoundException;

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
    public function getObject($name)
    {
        foreach ($this->getObjects() as $object) {
            if ($object->getName() === $name) {
                return $object;
            }
        }

        throw new DefinitionNotFoundException($name);
    }

    /**
     * @return RelationDefinition
     *
     * @throws RelationNotFoundException
     */
    public function getRelation($id)
    {
        foreach ($this->getRelations() as $relation) {
            if ($relation->getId() == $id) {
                return $relation;
            }
        }

        throw new RelationNotFoundException($id);
    }

    public function isDeletable()
    {
        return count($this->getProjects()) == 0;
    }

    /**
     * Create a copy of this beam
     *
     * @return BeamDefinition
     */
    public function duplicate($name)
    {
        $me = $this->toArray();
        $me['name'] = $name;

        return self::createFromArray($me);
    }

    /**
     * Returns $this as an array
     */
    public function toArray()
    {
        return array(
            'name'      => $this->getName(),
            'objects'   => $this->getObjectsAsArray(),
            'relations' => $this->getRelationsAsArray()
            );
    }

    /**
     * Returns objects as array of ObjectDefinition attributes
     */
    public function getObjectsAsArray()
    {
        $objects = array();
        foreach ($this->getObjects() as $object) 
        {
            $objects[] = $object->toArray();
        }
        return $objects;
    }

    /**
     * Returns relations as array of RelationDefinition attributes
     */
    public function getRelationsAsArray()
    {
        $relations = array();
        foreach ($this->getRelations() as $relation) 
        {
            $relations[] = $relation->toArray();
        }
        return $relations;
    }

    /**
     * Create a beam based on an array
     *
     * @return BeamDefinition
     */
    public static function createFromArray($array)
    {
        $beam = self::create($array['name']);

        foreach ($array['objects'] as $object) {
            $beam->addObject(ObjectDefinition::createFromArray($object));
        }
        foreach ($array['relations'] as $relation) {
            $beam->addRelation(Relation::createFromArray($relation));
        }

        return $beam;
    }
}
