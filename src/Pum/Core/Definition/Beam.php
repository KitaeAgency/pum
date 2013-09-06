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
     * @var string
     */
    protected $icon;

    /**
     * @var string
     */
    protected $color;

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
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return Object
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @return Object
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Tests if beam has an object with given name.
     *
     * @param string $name name of object
     *
     * @return boolean
     */
    public function hasObject($name)
    {
        foreach ($this->getObjects() as $object) {
            if ($object->getName() === $name) {
                return true;
            }
        }

        return false;
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
     * @return Beam
     */
    public function addObject(ObjectDefinition $definition)
    {
        if ($this->hasObject($definition->getName())) {
            throw new \RuntimeException(sprintf('Object "%s" is already present in beam "%s".', $definition->getName(), $this->name));
        }

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
     * @return boolean
     */
    public function isDeletable()
    {
        return count($this->getProjects()) == 0;
    }

    /**
     * Create a copy of this beam
     *
     * @return BeamDefinition
     */
    public function duplicate()
    {
        return self::createFromArray($this->toArray());
    }

    /**
     * Returns $this as an array
     */
    public function toArray()
    {
        return array(
            'name'      => $this->getName(),
            'icon'      => $this->getIcon(),
            'color'     => $this->getColor(),
            'objects'   => $this->getObjectsAsArray(),
            'relations' => $this->getRelationsAsArray()
        );
    }

    /**
     * Returns objects as array of ObjectDefinition attributes.
     *
     * @return array
     */
    public function getObjectsAsArray()
    {
        $objects = array();
        foreach ($this->getObjects() as $object) {
            $objects[] = $object->toArray();
        }
        return $objects;
    }

    /**
     * Returns relations as array of RelationDefinition attributes.
     *
     * @return array
     */
    public function getRelationsAsArray()
    {
        $relations = array();
        foreach ($this->getRelations() as $relation) {
            $relations[] = $relation->toArray();
        }
        return $relations;
    }

    /**
     * Create a beam based on an array
     *
     * @return Beam
     */
    public static function createFromArray($array)
    {
        if (!$array || !is_array($array)) {
            throw new \InvalidArgumentException('Beam - An array is excepted');
        }

        $attributes = array(
            'name'      => 'string',
            'icon'      => 'string',
            'color'     => 'string',
            'objects'   => 'array',
            'relations' => 'array'
            );
        foreach ($attributes as $name => $type) {
            if(!isset($array[$name])) {
                throw new \InvalidArgumentException(sprintf('Beam - key "%s" is missing', $name));
            }
            $typeTest = "is_$type";
            if (!$typeTest($array[$name])) {
                throw new \InvalidArgumentException(sprintf('Beam - "%s" value must be %s', $name, $type));
            }
        }

        $beam = self::create($array['name'])
            ->setIcon($array['icon'])
            ->setColor($array['color'])
        ;

        foreach ($array['objects'] as $object) {
            $beam->addObject(ObjectDefinition::createFromArray($object));
        }
        foreach ($array['relations'] as $relation) {
            $beam->addRelation(Relation::createFromArray($relation));
        }

        return $beam;
    }
}
