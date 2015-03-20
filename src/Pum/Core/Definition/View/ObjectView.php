<?php

namespace Pum\Core\Definition\View;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\View\ObjectViewField;
use Pum\Core\Exception\DefinitionNotFoundException;

class ObjectView
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
     * @var ObjectViewNode
     */
    protected $view;

    /**
     * @param ObjectDefinition $objectDefinition
     * @param string $name name of the object view.
     */
    public function __construct(ObjectDefinition $objectDefinition = null, $name = null)
    {
        $this->objectDefinition  = $objectDefinition;
        $this->name    = $name;
        $this->private = false;
        $this->fields  = new ArrayCollection();
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
     * @return FormViewNode
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return FormView
     */
    public function setView(ObjectViewNode $view = null)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return ObjectView
     */
    public function removeField(ObjectViewField $field)
    {
        $this->getFields()->removeElement($field);
    }

    /**
     * @return ObjectView
     */
    public function removeFields()
    {
        $this->getFields()->clear();

        return $this;
    }

    /**
     * Returns the field mapped by a given label.
     *
     * @return ObjectViewField
     * @throws DefinitionNotFoundException
     */
    public function getField($label)
    {
        foreach ($this->getFields() as $field) {
            if ($label instanceof ObjectViewField && $field === $label) {
                return $field;
            } elseif ($label instanceof FieldDefinition && $field->getField() === $label) {
                return $field;
            } elseif (is_string($label) && $field->getLabel() === $label) {
                return $field;
            }
        }

        if ($label instanceof FieldDefinition) {
            $label = $label->getName();
        } elseif ($label instanceof ObjectViewField) {
            $label = $label->getLabel();
        }

        throw new DefinitionNotFoundException($label);
    }

    /**
     * @return ArrayCollection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return boolean
     */
    public function hasField($label)
    {
        try {
            $this->getField($label);

            return true;
        } catch (DefinitionNotFoundException $e) {
            return false;
        }
    }

    /**
     * @return ObjectView
     */
    public function addField(ObjectViewField $field)
    {
        $this->getFields()->add($field);
        $field->setObjectView($this);

        return $this;
    }

    /**
     * @return ObjectView
     */
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

        $this->addField(new ObjectViewField($label, $field, $view, $sequence));

        return $this;
    }

    /**
     * @return Boolean
     */
    public function hasViewTab($nodeId)
    {
        if (null === $root = $this->getView()) {
            return false;
        }

        foreach ($root->getChildren() as $node) {
            if ($nodeId == $node->getId() && $node::TYPE_TAB == $node->getType()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Integer or null
     */
    public function getDefaultViewTab()
    {
        if (null === $root = $this->getView()) {
            return null;
        }

        foreach ($root->getChildren() as $node) {
            if ($node::TYPE_TAB == $node->getType()) {
                return $node->getId();
            }
        }

        return null;
    }

    /**
     * @return String
     */
    public function getDefaultViewTabType($nodeId)
    {
        if (null !== $root = $this->getView()) {
            foreach ($root->getChildren() as $node) {
                if ($nodeId == $node->getId() && $node::TYPE_TAB == $node->getType() || null === $nodeId) {
                    foreach ($node->getChildren() as $child) {
                        switch ($child->getType()) {
                            case $child::TYPE_GROUP_FIELD:
                                return array('regularFields', null);
                            break;

                            case $child::TYPE_FIELD:
                                if (null !== $child->getObjectViewField() && 'tab' == $child->getObjectViewField()->getOption('form_type')) {
                                    return array('relationFields', $child->getObjectViewField());
                                }

                                return array('regularFields', null);
                            break;
                        }

                        break;
                    }
                }
            }
        }

        return array('regularFields', null);
    }

    /**
     * @return Integer
     */
    public function countTabs()
    {
        if (null === $root = $this->getView()) {
            return 0;
        }

        $count = 0;

        foreach ($root->getChildren() as $node) {
            if ($node::TYPE_TAB == $node->getType()) {
                $count++;
            }
        }

        return $count;
    }
}
