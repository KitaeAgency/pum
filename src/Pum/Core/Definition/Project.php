<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * A project.
 *
 * @Entity()
 * @Table(name="project")
 */
class Project
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
     * @ManyToMany(targetEntity="Beam")
     * @JoinTable(name="project_beam",
     *      joinColumns={@JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="beam_id", referencedColumnName="id")}
     * )
     */
    protected $beams;

    /**
     * Constructor.
     */
    public function __construct($name = null)
    {
        $this->name   = $name;
        $this->beams = new ArrayCollection();
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
    public function addBeam(Beam $beam)
    {
        $this->getBeams()->add($beam);

        return $this;
    }

    /**
     * @return Beam
     */
    public function removeBeam(Beam $beam)
    {
        $this->getBeams()->removeElement($beam);
    }

    /**
     * @return array
     */
    public function getObjects()
    {
        return $this->objects;
    }
}
