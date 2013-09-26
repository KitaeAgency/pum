<?php

namespace Pum\Core\Definition\View;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\View\FormViewField;
use Pum\Core\Exception\DefinitionNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class FormView
{
    const DEFAULT_NAME = 'Default';
    /**
     * @var string
     */
    protected $id;

    /**
     * @var ObjectDefinition
     */
    protected $objectDefinition;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var boolean
     */
    protected $private;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @param ObjectDefinition $objectDefinition
     * @param string $name name of the form view.
     */
    public function __construct(ObjectDefinition $objectDefinition = null, $name = null)
    {
        $this->objectDefinition  = $objectDefinition;
        $this->name    = $name;
        $this->private = false;
        $this->fields = new ArrayCollection();
    }

    /**
     * @return ObjectDefinition
     */
    public function getObjectDefinition()
    {
        return $this->objectDefinition;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ObjectView
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * @return ObjectView
     */
    public function setPrivate($private)
    {
        $this->private = (boolean)$private;

        return $this;
    }

    /**
     * @return ObjectView
     */
    public function removeField(FormViewField $field)
    {
        $this->getFields()->removeElement($field);
    }

    /**
     * @return FormView
     */
    public function removeFields()
    {
        $this->getFields()->clear();

        return $this;
    }

    /**
     * Returns the column mapped by a given column.
     *
     * @return FormViewField
     */
    public function getField($label)
    {
        foreach ($this->getFields() as $field) {
            if ($label instanceof FormViewField && $field === $label) {
                return $field;
            } elseif ($label instanceof FieldDefinition && $field->getField() === $label) {
                return $field;
            } elseif (is_string($label) && $field->getLabel() === $label) {
                return $field;
            }
        }

        if ($label instanceof FieldDefinition) {
            $label = $label->getName();
        } elseif ($label instanceof FormViewField) {
            $label = $label->getLabel();
        }

        throw new DefinitionNotFoundException($label);
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function hasField($label)
    {
        try {
            $this->getField($label);

            return true;
        } catch (DefinitionNotFoundException $e) {
            return false;
        }
    }

    public function addField(FormViewField $field)
    {
        $this->getFields()->add($field);
        $field->setFormView($this);

        return $this;
    }

    public function createField($label, $field = null, $view = 'default', $sequence = null)
    {
        if (null === $field) {
            $field = $label;
        }

        if (is_string($label) && $this->getObjectDefinition()) {
            $field = $this->getObjectDefinition()->getField($label);
        }

        if (!$field instanceof FieldDefinition) {
            throw new \InvalidArgumentException('Expected a FieldDefinition, got a "%s".', is_object($field) ? get_class($field) : gettype($field));
        }

        $this->fields->add($field = new FormViewField($label, $field, $view, $sequence));
        $field->setFormView($this);

        return $this;
    }
}
