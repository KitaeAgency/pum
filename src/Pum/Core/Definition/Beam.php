<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Event\BeamEvent;
use Pum\Core\Event\ObjectDefinitionEvent;
use Pum\Core\Events;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Relation\RelationSchema;
use Pum\Core\Schema\SchemaInterface;

/**
 * A beam.
 */
class Beam extends EventObject
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $seed;

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
    protected $projects;

    /**
     * Constructor.
     */
    public function __construct($name = null)
    {
        $this->seed      = md5(mt_rand());
        $this->name      = $name;
        $this->objects   = new ArrayCollection();
        $this->projects  = new ArrayCollection();

        $this->raise(Events::BEAM_CREATE, new BeamEvent($this));
    }

    /**
     * @param null $name
     * @return Beam
     */
    public static function create($name = null)
    {
        return new self($name);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSeed()
    {
        return $this->seed;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        if ($name !== $this->name) {
            $this->raiseOnce(Events::BEAM_UPDATE, new BeamEvent($this));
        }

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
     * @param $icon
     * @return $this
     */
    public function setIcon($icon)
    {
        if ($icon !== $this->icon) {
            $this->raiseOnce(Events::BEAM_UPDATE, new BeamEvent($this));
        }

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
     * @param $color
     * @return $this
     */
    public function setColor($color)
    {
        if ($color !== $this->color) {
            $this->raiseOnce(Events::BEAM_UPDATE, new BeamEvent($this));
        }

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
     * @param $name
     * @return mixed
     * @throws \Pum\Core\Exception\DefinitionNotFoundException
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
     * @param ObjectDefinition $definition
     * @return $this
     * @throws \RuntimeException
     */
    public function addObject(ObjectDefinition $definition)
    {
        if ($this->hasObject($definition->getName())) {
            throw new \RuntimeException(sprintf('Object "%s" is already present in beam "%s".', $definition->getName(), $this->name));
        }

        $this->getObjects()->add($definition);

        $this->raise(Events::BEAM_OBJECT_ADDED, new ObjectDefinitionEvent($definition));

        $definition->setBeam($this);

        return $this;
    }

    /**
     * @param ObjectDefinition $definition
     * @return $this
     */
    public function removeObject(ObjectDefinition $definition)
    {
        if ($this->objects->contains($definition)) {
            $this->raise(Events::BEAM_OBJECT_REMOVED, new ObjectDefinitionEvent($definition));
            $this->getObjects()->removeElement($definition);
        }

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * @return ArrayCollection
     */
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
     * @return array
     */
    public function toArray()
    {
        return array(
            'name'      => $this->getName(),
            'seed'      => $this->getSeed(),
            'icon'      => $this->getIcon(),
            'color'     => $this->getColor(),
            'objects'   => $this->getObjectsAsArray()
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
     * Create a beam based on an array
     *
     * @param $array
     * @return mixed
     * @throws \InvalidArgumentException
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
            );
        foreach ($attributes as $name => $type) {
            if (!isset($array[$name])) {
                throw new \InvalidArgumentException(sprintf('Beam - key "%s" is missing', $name));
            }
            $typeTest = "is_$type";
            if (!$typeTest($array[$name])) {
                throw new \InvalidArgumentException(sprintf('Beam - "%s" value must be %s', $name, $type));
            }
        }

        $beam = self::create($array['name'])
            ->setIcon($array['icon'])
            ->setColor($array['color']);
        $beam->seed = $array['seed'];

        foreach ($array['objects'] as $object) {
            $beam->addObject(ObjectDefinition::createFromArray($object));
        }

        return $beam;
    }

    /**
     * Returns a unique signature of the beam
     *
     * @return string
     */
    public function getSignature()
    {
        return md5($this->seed . json_encode($this->toArray()));
    }

    /**
     * Return all beam relations
     *
     * @return array
     */
    public function getRelations()
    {
        $relations = array();

        foreach ($this->getObjects() as $object) {
            $objectRelations = array();
            foreach ($object->getRelations() as $relation) {
                if (!RelationSchema::isExistedInverseRelation($relations, $relation)) {
                    $objectRelations[] = $relation;
                }
            }
            $relations = array_merge($objectRelations, $relations);
        }
        return $relations;
    }

    /**
     * @return bool
     */
    public function hasExternalRelations()
    {
        foreach ($this->getRelations() as $relation) {
            if ($relation->isExternal()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getExternalRelations()
    {
        $externals = array();
        foreach ($this->getRelations() as $relation) {
            if ($relation->isExternal()) {
                $externals[] = $relation;
            }
        }
        return $externals;
    }
}
