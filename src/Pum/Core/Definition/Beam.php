<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Event\BeamEvent;
use Pum\Core\Event\ObjectDefinitionEvent;
use Pum\Core\Events;
use Pum\Core\Exception\DefinitionNotFoundException;

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
     * @return Object
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
     * @return Object
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
     * @return Object
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
     * @return Object
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

        $this->raise(Events::BEAM_OBJECT_ADDED, new ObjectDefinitionEvent($definition));

        $definition->setBeam($this);

        return $this;
    }

    /**
     * @return Beam
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
     * @return array
     */
    public function getObjects()
    {
        return $this->objects;
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
}
