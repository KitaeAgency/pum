<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * A beam.
 *
 * @Entity()
 * @Table(name="beam")
 */
class Beam
{
    /**
     * @Id()
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @Column(type="string", length=64)
     */
    protected $name;

    /**
     * @var ArrayCollection
     *
     * @OneToMany(targetEntity="ObjectDefinition", mappedBy="beam", orphanRemoval=true, cascade={"persist", "remove"})
     */
    protected $objects;

    /**
     * Constructor.
     */
    public function __construct($name = null)
    {
        $this->name    = $name;
        $this->objects = new ArrayCollection();
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
