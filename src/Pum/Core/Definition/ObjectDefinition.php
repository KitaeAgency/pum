<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Exception\TableViewNotFoundException;

/**
 * Definition of a dynamic object.
 */
class ObjectDefinition
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
    protected $fields;

    /**
     * @var string
     *
     * @Column(type="string", length=64, nullable=true)
     */
    protected $classname;

    /**
     * @var Beam
     */
    protected $beam;

    /**
     * @var ArrayCollection
     */
    protected $tableViews;

    /**
     * Constructor.
     */
    public function __construct($name = null)
    {
        $this->name   = $name;
        $this->fields = new ArrayCollection();
        $this->tableViews = new ArrayCollection();
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
    public function getBeam()
    {
        return $this->beam;
    }

    /**
     * @return Object
     */
    public function setBeam(Beam $beam)
    {
        $this->beam = $beam;

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
     * @return FieldDefinition
     *
     * @throws DefinitionNotFoundException
     */
    public function getField($name)
    {
        foreach ($this->getFields() as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        throw new DefinitionNotFoundException($name);
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
     * Removes a field to the object.
     *
     * @param FieldDefinition $field field to remove.
     *
     * @return Object
     */
    public function removeField(FieldDefinition $field)
    {
        $this->fields->removeElement($field);

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
    public function createField($name, $type, array $typeOptions = array())
    {
        if ($this->hasField($name)) {
            throw new \RuntimeException(sprintf('Field "%s" is already present in object "%s".', $name, $this->name));
        }

        $this->addField(new FieldDefinition($name, $type, $typeOptions));

        return $this;
    }

    /**
     * @return string
     */
    public function getClassname()
    {
        return $this->classname;
    }

    /**
     * @return Object
     */
    public function setClassname($classname)
    {
        $this->classname = $classname;

        return $this;
    }

    /**
     * Returns relations associated to this definition in the current beam.
     */
    public function getRelationsInBeam()
    {
        $relations = $this->getBeam()->getRelations();
        $result = array();

        foreach ($relations as $relation) {
            if ($relation->getFrom() === $this->getName()) {
                $result[] = $relation;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getTableViews()
    {
        return $this->tableViews;
    }

    /**
     * @param string $name the name of table view to search for
     *
     * @return TableView
     *
     * @throws TableViewNotFoundException
     */
    public function getTableView($name)
    {
        foreach ($this->tableViews as $tableView) {
            if ($tableView->getName() == $name) {
                return $tableView;
            }
        }

        throw new TableViewNotFoundException($this, $name);
    }

    /**
     * Creates a new table view on the beam.
     *
     * @return TableView
     */
    public function createTableView($name = null)
    {
        $tableView = new TableView($this, $name);
        $this->getTableViews()->add($tableView);

        return $tableView;
    }

    /**
     * Creates a default table view on the beam.
     *
     * @return TableView
     */
    public function createDefaultTableView()
    {
        $tableView = $this->createTableView('Default');

        foreach ($this->getFields() as $field) {
            $tableView->addColumn($field->getName());
        }

        return $tableView;
    }

    /**
     * Returns $this as an array
     */
    public function toArray()
    {
        return array(
            'name'   => $this->getName(),
            'fields' => $this->getFieldsAsArray()
            );
    }

    /**
     * Returns fields as array of FieldDefinition attributes
     */
    public function getFieldsAsArray()
    {
        $fields = array();
        foreach ($this->getFields() as $field) 
        {
            $fields[] = $field->toArrAy();
        }
        return $fields;
    }

    /**
     * Create a object based on an array
     *
     * @return ObjectDefinition
     */
    public static function createFromArray($array)
    {
        if (!$array || !is_array($array)) {
            throw new \InvalidArgumentException('ObjectDefinition - An array is excepted');
        }
        
        $attributes = array(
            'name'   => 'string',
            'fields' => 'array'
            );
        foreach ($attributes as $name => $type) {
            if(!isset($array[$name])) {
                throw new \InvalidArgumentException(sprintf('ObjectDefinition - key "%s" is missing', $name));
            }
            $typeTest = "is_$type";
            if (!$typeTest($array[$name])) {
                throw new \InvalidArgumentException(sprintf('ObjectDefinition - "%s" value must be %s', $name, $type));
            }
        }

        $object = self::create($array['name']);
        
        foreach ($array['fields'] as $field) {
            $object->addField(FieldDefinition::createFromArray($field));
        }
        
        return $object;
    }
}
