<?php

namespace Pum\Core\Definition\View;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\View\FormViewField;
use Pum\Core\Exception\DefinitionNotFoundException;

class FormView
{
    const DEFAULT_NAME = 'Default';

    /**
     * @var int
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
     * @var ArrayCollection
     */
    protected $fields;

    /**
     * @var FormViewNode
     */
    protected $view;

    /**
     * @param ObjectDefinition $objectDefinition
     * @param string $name name of the form view.
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return FormView
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
     * @return FormView
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
    public function setView(FormViewNode $view = null)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return FormView
     */
    public function createRootViewNode()
    {
        $formViewNode = new FormViewNode();
        $formViewNode
            ->setName(FormViewNode::TYPE_ROOT)
            ->setType(FormViewNode::TYPE_ROOT)
        ;

        $this->setView($formViewNode);

        return $this;
    }

    /**
     * @return FormView
     */
    public function removeField(FormViewField $field)
    {
        $this->getFields()->removeElement($field);

        return $this;
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
     * Returns the field mapped by a given label.
     *
     * @return mixed
     * @throws DefinitionNotFoundException
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
     * @return FormView
     */
    public function addField(FormViewField $field)
    {
        $this->getFields()->add($field);
        $field->setFormView($this);

        return $this;
    }

    /**
     * @return FormView
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

        $this->addField(new FormViewField($label, $field, $view, $sequence));

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
                                if (null !== $child->getFormViewField() && 'tab' == $child->getFormViewField()->getOption('form_type')) {
                                    return array('relationFields', $child->getFormViewField());
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
