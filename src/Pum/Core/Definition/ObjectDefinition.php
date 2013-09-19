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
use Pum\Core\Exception\ObjectViewNotFoundException;
use Pum\Core\Exception\FormViewNotFoundException;

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
     * @var ArrayCollection
     */
    protected $objectViews;

    /**
     * @var ArrayCollection
     */
    protected $formViews;

    /**
     * Constructor.
     */
    public function __construct($name = null)
    {
        $this->name   = $name;
        $this->fields = new ArrayCollection();
        $this->tableViews  = new ArrayCollection();
        $this->objectViews = new ArrayCollection();
        $this->formViews   = new ArrayCollection();
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
     * Tests if object has a tableView with given name.
     *
     * @param string $name name of field
     *
     * @return boolean
     */
    public function hasTableView($name)
    {
        foreach ($this->tableViews as $tableView) {
            if ($tableView->getName() == $name) {
                return true;
            }
        }

        return false;
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
     * Add a tableView on the ObjectDefinition.
     *
     * @return ObjectDefinition
     */
    public function addTableView(TableView $tableView)
    {
        $this->getTableViews()->add($tableView);

        return $this;
    }

    /**
     * Remove a tableView on the ObjectDefinition.
     *
     * @return ObjectDefinition
     */
    public function removeTableView(TableView $tableView)
    {
        $this->tableViews->removeElement($tableView);

        return $this;
    }

    /**
     * Creates a new table view on the beam.
     *
     * @return TableView
     */
    public function createTableView($name = null)
    {
        if ($this->hasTableView($name)) {
            throw new \RuntimeException(sprintf('TableView "%s" is already present in object "%s".', $name, $this->name));
        }

        $tableView = new TableView($this, $name);
        $this->addTableView($tableView);

        return $tableView;
    }

    /**
     * Creates a default table view on the beam.
     *
     * @return TableView
     */
    public function createDefaultTableView($defaultName = TableView::DEFAULT_NAME)
    {
        $tableView = $this->createTableView($defaultName);

        foreach ($this->getFields() as $field) {
            $tableView->addColumn($field->getName());
        }

        return $tableView;
    }

    /**
     * @return ArrayCollection
     */
    public function getObjectViews()
    {
        return $this->objectViews;
    }

    /**
     * Tests if object has a ObjectView with given name.
     *
     * @param string $name name of field
     *
     * @return boolean
     */
    public function hasObjectView($name)
    {
        foreach ($this->objectViews as $objectView) {
            if ($objectView->getName() == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $name the name of object view to search for
     *
     * @return ObjectView
     *
     * @throws ObjectViewNotFoundException
     */
    public function getObjectView($name)
    {
        foreach ($this->objectViews as $objectView) {
            if ($objectView->getName() == $name) {
                return $objectView;
            }
        }

        throw new ObjectViewNotFoundException($this, $name);
    }

    /**
     * Add a ObjectView on the ObjectDefinition.
     *
     * @return ObjectDefinition
     */
    public function addObjectView(ObjectView $objectView)
    {
        $this->getObjectViews()->add($objectView);

        return $this;
    }

    /**
     * Remove a ObjectView on the ObjectDefinition.
     *
     * @return ObjectDefinition
     */
    public function removeObjectView(ObjectView $objectView)
    {
        $this->objectViews->removeElement($objectView);

        return $this;
    }

    /**
     * Creates a new object view on the beam.
     *
     * @return ObjectView
     */
    public function createObjectView($name = null)
    {
        if ($this->hasObjectView($name)) {
            throw new \RuntimeException(sprintf('ObjectView "%s" is already present in object "%s".', $name, $this->name));
        }

        $objectView = new ObjectView($this, $name);
        $this->addObjectView($objectView);

        return $objectView;
    }

    /**
     * Creates a default object view on the beam.
     *
     * @return ObjectView
     */
    public function createDefaultObjectView($defaultName = ObjectView::DEFAULT_NAME)
    {
        $objectView = $this->createObjectView($defaultName);

        foreach ($this->getFields() as $field) {
            $objectView->addColumn($field->getName());
        }

        return $objectView;
    }

    /**
     * @return ArrayCollection
     */
    public function getFormViews()
    {
        return $this->formViews;
    }

    /**
     * Tests if form has a FormView with given name.
     *
     * @param string $name name of field
     *
     * @return boolean
     */
    public function hasFormView($name)
    {
        foreach ($this->formViews as $formView) {
            if ($formView->getName() == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $name the name of form view to search for
     *
     * @return FormView
     *
     * @throws FormViewNotFoundException
     */
    public function getFormView($name)
    {
        foreach ($this->formViews as $formView) {
            if ($formView->getName() == $name) {
                return $formView;
            }
        }

        throw new FormViewNotFoundException($this, $name);
    }

    /**
     * Add a FormView on the FormDefinition.
     *
     * @return FormDefinition
     */
    public function addFormView(FormView $formView)
    {
        $this->getFormViews()->add($formView);

        return $this;
    }

    /**
     * Remove a FormView on the FormDefinition.
     *
     * @return FormDefinition
     */
    public function removeFormView(FormView $formView)
    {
        $this->formViews->removeElement($formView);

        return $this;
    }

    /**
     * Creates a new form view on the beam.
     *
     * @return FormView
     */
    public function createFormView($name = null)
    {
        if ($this->hasFormView($name)) {
            throw new \RuntimeException(sprintf('FormView "%s" is already present in form "%s".', $name, $this->name));
        }

        $formView = new FormView($this, $name);
        $this->addFormView($formView);

        return $formView;
    }

    /**
     * Creates a default form view on the beam.
     *
     * @return FormView
     */
    public function createDefaultFormView($defaultName = FormView::DEFAULT_NAME)
    {
        $formView = $this->createFormView($defaultName);

        foreach ($this->getFields() as $field) {
            $formView->addRow($field->getName());
        }

        return $formView;
    }

    /**
     * Returns $this as an array
     */
    public function toArray()
    {
        return array(
            'name'      => $this->getName(),
            'classname' => $this->getClassname(),
            'fields'    => $this->getFieldsAsArray(),
        );
    }

    /**
     * Returns fields as array of FieldDefinition attributes
     */
    public function getFieldsAsArray()
    {
        $fields = array();
        foreach ($this->getFields() as $field) {
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

        $object = self::create($array['name'])
            ->setClassname($array['classname'])
        ;
        
        foreach ($array['fields'] as $field) {
            $object->addField(FieldDefinition::createFromArray($field));
        }
        
        return $object;
    }
}
