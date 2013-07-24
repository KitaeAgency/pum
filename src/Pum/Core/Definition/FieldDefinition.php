<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * Definition of a dynamic object.
 */
class FieldDefinition
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var ObjectDefinition
     */
    protected $object;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $isUnique;

    /**
     * Constructor.
     */
    public function __construct($name = null, $type = null)
    {
        $this->name     = $name;
        $this->type     = $type;
        $this->isUnique = false;
    }

    /**
     * @return ObjectDefinition
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
     * @return ObjectDefinition
     */
    public function setObject(ObjectDefinition $object = null)
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
     * @return boolean
     */
    public function isUnique()
    {
        return $this->isUnique;
    }

    /**
     * @return ObjectField
     */
    public function setUnique($isUnique)
    {
        $this->isUnique = $isUnique;

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

    public function getMetadataDefinition()
    {
        return array(
            'fieldName' => $this->getName(),
            'type'      => $this->getType(),
        );
    }
}
