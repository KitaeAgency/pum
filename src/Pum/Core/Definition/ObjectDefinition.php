<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Definition of a dynamic object.
 *
 * @Entity()
 * @Table(name="definition_object")
 */
class ObjectDefinition
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
     * @OneToMany(targetEntity="FieldDefinition", mappedBy="object", orphanRemoval=true, cascade={"persist", "remove"})
     */
    protected $fields;

    /**
     * Constructor.
     */
    public function __construct($name = null)
    {
        $this->name   = $name;
        $this->fields = new ArrayCollection();
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
     * @return ArrayCollection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Tests if object has a field with given name.
     *
     * @param string $name name of field
     *
     * @return boolean
     */
    public function hasField($name)
    {
        foreach ($this->fields as $field) {
            if ($field->getName() == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds a field to the object.
     *
     * @param FieldDefinition $field field to add.
     *
     * @return Object
     */
    public function addField(FieldDefinition $field)
    {
        $field->setObject($this);
        $this->fields->add($field);

        return $this;
    }

    /**
     * Creates a field on the object on the fly.
     *
     * @param string $name name of new field to create
     * @param string $type type of field
     *
     * @return Object
     */
    public function createField($name, $type)
    {
        if ($this->hasField($name)) {
            throw new \RuntimeException(sprintf('Field "%s" is already present in object "%s".', $name, $this->name));
        }

        $this->addField(new FieldDefinition($name, $type));

        return $this;
    }
}
