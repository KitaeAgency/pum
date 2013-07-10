<?php

namespace Pum\DemoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Definition of a dynamic object.
 *
 * @ORM\Entity()
 * @ORM\Table(name="object_field")
 */
class ObjectField
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Object", inversedBy="fields")
     * @ORM\JoinColumn(name="object_id")
     */
    protected $object;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64)
     */
    protected $type;

    /**
     * Constructor.
     */
    public function __construct($name = null, $type = null)
    {
        $this->name   = $name;
        $this->type   = $type;
    }

    /**
     * @return Object
     */
    public static function create($name = null, $type = null)
    {
        return new self($name, $type);
    }

    /**
     * @return Object
     */
    public function getObject()
    {
        return $this->name;
    }

    /**
     * Changes associated object.
     *
     * @return ObjectField
     */
    public function setObject(Object $object = null)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ObjectField
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return ObjectField
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
