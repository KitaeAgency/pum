<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Definition of a dynamic object.
 *
 * @Entity()
 * @Table(name="definition_object_field")
 */
class FieldDefinition
{
    /**
     * @Id()
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="ObjectDefinition", inversedBy="fields")
     * @JoinColumn(name="object_id")
     */
    protected $object;

    /**
     * @var string
     *
     * @Column(type="string", length=64)
     */
    protected $name;

    /**
     * @var string
     *
     * @Column(type="string", length=64)
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
