<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Definition\View\FormView;
use Pum\Core\Definition\View\FormViewField;
use Pum\Core\Definition\View\ObjectView;
use Pum\Core\Definition\View\TableView;
use Pum\Core\Definition\View\TableViewField;
use Pum\Core\Event\FieldDefinitionEvent;
use Pum\Core\Event\ObjectDefinitionEvent;
use Pum\Core\Events;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Extension\Util\Namer;

/**
 * Definition of a dynamic object.
 */
class ObjectDefinition extends EventObject
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
     */
    protected $classname;

    /**
     * @var string
     *
     */
    protected $repositoryClass;

    /**
     * @var boolean
     */
    protected $seoEnabled;

    /**
     * @var FieldDefinition
     */
    protected $seoField;

    /**
     * @var integer
     */
    protected $seoOrder;

    /**
     * @var string
     */
    protected $seoTemplate;

    /**
     * @var boolean
     */
    protected $securityUserEnabled;

    /**
     * @var FieldDefinition
     */
    protected $securityUsernameField;

    /**
     * @var FieldDefinition
     */
    protected $securityPasswordField;

    /**
     * @var boolean
     */
    protected $searchEnabled;

    /**
     * @var ArrayCollection
     */
    protected $searchFields;

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

        $this->seoEnabled          = false;
        $this->securityUserEnabled = false;
        $this->searchEnabled       = false;

        $this->fields       = new ArrayCollection();
        $this->tableViews   = new ArrayCollection();
        $this->objectViews  = new ArrayCollection();
        $this->formViews    = new ArrayCollection();
        $this->searchFields = new ArrayCollection();

        $this->raise(Events::OBJECT_DEFINITION_CREATE, new ObjectDefinitionEvent($this));
    }

    /**
     * Prepare the future.
     */
    public function getBehaviors()
    {
        $behaviors = array();

        if ($this->seoEnabled) {
            $behaviors[] = 'seo';
        }

        if ($this->securityUserEnabled) {
            $behaviors[] = 'security_user';
        }

        if ($this->searchEnabled) {
            $behaviors[] = 'searchable';
        }

        return $behaviors;
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

    public function getLowercaseName()
    {
        return Namer::toLowercase($this->name);
    }

    /**
     * @return Object
     */
    public function setName($name)
    {
        if ($name !== $this->name) {
            $this->raise(Events::OBJECT_DEFINITION_UPDATE, new ObjectDefinitionEvent($this));
        }

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
        if (null !== $this->beam) {
            throw new \InvalidArgumentException(sprintf('Cannot change beam of an object definition. MAKES NO SENSE!'));
        }
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
        if ($this->fields->contains($field)) {
            return;
        }

        $this->raise(Events::OBJECT_DEFINITION_FIELD_ADDED, new FieldDefinitionEvent($field));
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
        if ($classname === $this->classname) {
            return $this;
        }

        $this->raise(Events::OBJECT_DEFINITION_UPDATE, new ObjectDefinitionEvent($this));
        $this->classname = $classname;

        return $this;
    }

    /**
     * @return string
     */
    public function getRepositoryClass()
    {
        return $this->repositoryClass;
    }

    /**
     * @return Object
     */
    public function setRepositoryClass($repositoryClass)
    {
        if ($repositoryClass === $this->repositoryClass) {
            return $this;
        }

        $this->raise(Events::OBJECT_DEFINITION_UPDATE, new ObjectDefinitionEvent($this));
        $this->repositoryClass = $repositoryClass;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSeoEnabled()
    {
        return $this->seoEnabled;
    }

    /**
     * @return ObjectDefinition
     */
    public function setSeoEnabled($seoEnabled)
    {
        if ($seoEnabled === $this->seoEnabled) {
            return $this;
        }

        $this->raise(Events::OBJECT_DEFINITION_UPDATE, new ObjectDefinitionEvent($this));
        $this->seoEnabled = $seoEnabled;

        return $this;
    }

    /**
     * @return FieldDefinition|null
     */
    public function getSeoField()
    {
        return $this->seoField;
    }

    /**
     * @return ObjectDefinition
     */
    public function setSeoField(FieldDefinition $seoField)
    {
        if ($this->seoField !== $seoField) {
            $this->raise(Events::OBJECT_DEFINITION_UPDATE, new ObjectDefinitionEvent($this));
            $this->seoField = $seoField;
        }

        return $this;
    }

    /**
     * @return integer|null
     */
    public function getSeoOrder()
    {
        return $this->seoOrder;
    }

    /**
     * @return ObjectDefinition
     */
    public function setSeoOrder($seoOrder)
    {
        if ($this->seoOrder !== $seoOrder) {
            $this->raise(Events::OBJECT_DEFINITION_UPDATE, new ObjectDefinitionEvent($this));
            $this->seoOrder = $seoOrder;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSeoTemplate()
    {
        return $this->seoTemplate;
    }

    /**
     * @return ObjectDefinition
     */
    public function setSeoTemplate($seoTemplate)
    {
        if ($this->seoTemplate !== $seoTemplate) {
            $this->raise(Events::OBJECT_DEFINITION_UPDATE, new ObjectDefinitionEvent($this));
            $this->seoTemplate = $seoTemplate;
        }


        return $this;
    }

    /**
     * @return ObjectDefinition
     */
    public function setSecurityUserEnabled($securityUserEnabled = true)
    {
        if ($this->securityUserEnabled !== $securityUserEnabled) {
            $this->raise(Events::OBJECT_DEFINITION_UPDATE, new ObjectDefinitionEvent($this));
            $this->securityUserEnabled = $securityUserEnabled;
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSecurityUserEnabled()
    {
        return $this->securityUserEnabled;
    }

    /**
     * @return ObjectDefinition
     */
    public function setSecurityUsernameField(FieldDefinition $securityUsernameField)
    {
        if ($this->securityUsernameField !== $securityUsernameField) {
            $this->raise(Events::OBJECT_DEFINITION_UPDATE, new ObjectDefinitionEvent($this));
            $this->securityUsernameField = $securityUsernameField;
        }

        return $this;
    }

    /**
     * @return FieldDefinition|null
     */
    public function getSecurityUsernameField()
    {
        return $this->securityUsernameField;
    }

    /**
     * @return ObjectDefinition
     */
    public function setSecurityPasswordField(FieldDefinition $securityPasswordField)
    {
        if ($this->securityPasswordField !== $securityPasswordField) {
            $this->raise(Events::OBJECT_DEFINITION_UPDATE, new ObjectDefinitionEvent($this));
            $this->securityPasswordField = $securityPasswordField;
        }

        $this->securityPasswordField = $securityPasswordField;

        return $this;
    }

    /**
     * @return FieldDefinition|null
     */
    public function getSecurityPasswordField()
    {
        return $this->securityPasswordField;
    }

    /**
     * @return ObjectDefinition
     */
    public function setSearchEnabled($searchEnabled)
    {
        if ($this->searchEnabled !== $searchEnabled) {
            $this->raise(Events::OBJECT_DEFINITION_SEARCH_UPDATE, new ObjectDefinitionEvent($this));
            $this->searchEnabled = $searchEnabled;
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSearchEnabled()
    {
        return $this->searchEnabled;
    }

    /**
     * @return ArrayCollection
     */
    public function getSearchFields()
    {
        return $this->searchFields;
    }

    /**
     * @return ObjectDefintion
     */
    public function addSearchField(SearchField $searchField)
    {
        if (!$this->searchFields->contains($searchField)) {
            $this->raise(Events::OBJECT_DEFINITION_SEARCH_UPDATE, new ObjectDefinitionEvent($this));
            $this->searchFields->add($searchField);
            $searchField->setObjectDefinition($this);
        }

        return $this;
    }

    /**
     * @return ObjectDefinition
     */
    public function removeSearchField(SearchField $searchField)
    {
        if ($this->searchFields->contains($searchField)) {
            $this->raise(Events::OBJECT_DEFINITION_SEARCH_UPDATE, new ObjectDefinitionEvent($this));
            $this->searchFields->removeElement($searchField);
            $searchField->setObjectDefinition($this);
        }

        return $this;
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
     * @throws DefinitionNotFoundException
     */
    public function getTableView($name)
    {
        foreach ($this->tableViews as $tableView) {
            if ($tableView->getName() == $name) {
                return $tableView;
            }
        }

        throw new DefinitionNotFoundException($name);
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

        $i = 1;
        foreach ($this->getFields() as $field) {
            $tableView->createColumn($field->getName(), $field, TableViewField::DEFAULT_VIEW, $i++);
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
     * @throws DefinitionNotFoundException
     */
    public function getObjectView($name)
    {
        foreach ($this->objectViews as $objectView) {
            if ($objectView->getName() == $name) {
                return $objectView;
            }
        }

        throw new DefinitionNotFoundException($name);
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

        $i = 1;
        foreach ($this->getFields() as $field) {
            $objectView->createField($field->getName(), $field, 'default', $i++);
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
     * @throws DefinitionNotFoundException
     */
    public function getFormView($name)
    {
        foreach ($this->formViews as $formView) {
            if ($formView->getName() == $name) {
                return $formView;
            }
        }

        throw new DefinitionNotFoundException($name);
    }

    /**
     * Add a FormView on the ObjectDefinition.
     *
     * @return ObjectDefinition
     */
    public function addFormView(FormView $formView)
    {
        $this->getFormViews()->add($formView);

        return $this;
    }

    /**
     * Remove a FormView from the ObjectDefinition.
     *
     * @return ObjectDefinition
     */
    public function removeFormView(FormView $formView)
    {
        $this->getFormViews()->removeElement($formView);

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

        $i = 1;
        foreach ($this->getFields() as $field) {
            $formView->createField($field->getName(), $field, 'default', $i++);
        }

        return $formView;
    }

    /**
     * Returns $this as an array
     */
    public function toArray()
    {
        return array(
            'name'                    => $this->getName(),
            'classname'               => $this->getClassname(),
            'repository_class'        => $this->getRepositoryClass(),
            'fields'                  => $this->getFieldsAsArray(),
            'seo_enabled'             => $this->seoEnabled,
            'seo_field'               => $this->seoField ? $this->seoField->getName() : null,
            'seo_order'               => $this->seoOrder,
            'security_user_enabled'   => $this->isSecurityUserEnabled(),
            'security_username_field' => $this->securityUsernameField ? $this->securityUsernameField->getName() : null,
            'security_password_field' => $this->securityPasswordField ? $this->securityPasswordField->getName() : null,
            'seo_template'            => $this->seoTemplate,
            'search_enabled'          => $this->searchEnabled,
            'search_fields'           => $this->searchEnabled ? $this->getSearchFieldsAsArray() : array()
        );
    }

    public function getSearchFieldsAsArray()
    {
        $result = array();

        foreach ($this->searchFields as $field) {
            $result[] = $field->toArray();
        }

        return $result;
    }

    /**
     * Returns fields as array of FieldDefinition attributes
     */
    public function getFieldsAsArray()
    {
        $fields = array();
        foreach ($this->getFields() as $field) {
            $fields[] = $field->toArray();
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

        if (isset($array['search_fields'])) {
            foreach ($array['search_fields'] as $field) {
                $name = $field['name'];

                $object->addSearchField(SearchField::createFromArray($field));
            }
        }

        $object
            ->setClassname(isset($array['classname']) ? $array['classname'] : null)
            ->setRepositoryClass(isset($array['repository_class']) ? $array['repository_class'] : null)
            ->setSeoEnabled(isset($array['seo_enabled']) ? $array['seo_enabled'] : false)
            ->setSecurityUserEnabled(isset($array['security_user_enabled']) ? $array['security_user_enabled'] : false)
            ->setSeoOrder(isset($array['seo_order']) ? $array['seo_order'] : null)
            ->setSeoTemplate(isset($array['seo_template']) ? $array['seo_template'] : false)
            ->setSearchEnabled(isset($array['search_enabled']) ? $array['search_enabled'] : false)
        ;

        if (isset($array['seo_enabled']) && $array['seo_enabled'] && isset($array['seo_field']) && $object->hasField($array['seo_field'])) {
            $object->setSeoField($object->getField($array['seo_field']));
        }

        if (isset($array['security_user_enabled']) && $array['security_user_enabled'] && isset($array['security_username_field']) && $object->hasField($array['security_username_field'])) {
            $object->setSecurityUsernameField($object->getField($array['security_username_field']));
        }

        if (isset($array['security_user_enabled']) && $array['security_user_enabled'] && isset($array['security_password_field']) && $object->hasField($array['security_password_field'])) {
            $object->setSecurityPasswordField($object->getField($array['security_password_field']));
        }

        return $object;
    }
}
