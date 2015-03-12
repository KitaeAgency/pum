<?php

namespace Pum\Core\Definition\View;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Exception\DefinitionNotFoundException;

class FormViewNode extends AbstractViewNode
{
    /**
     * @var FormViewField
     */
    protected $formViewField;

    /**
     * Constructor.
     */
    public function __construct($name = null, $type = null, $sequence = null, FormViewField $formViewField = null)
    {
        $this->name          = $name;
        $this->formViewField = $formViewField;
        $this->options       = array();
        $this->children      =  new ArrayCollection();

        $this->setType($type);
    }

    /**
     * @return FormViewField
     */
    public static function create($name = null, $type = null, $sequence = null, FormViewField $formViewField = null)
    {
        return new self($name, $type, $sequence, $formViewField);
    }

    /**
     * @return FormViewNode
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return FormViewNode
     */
    public function setParent(FormViewNode $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return FormViewField
     */
    public function getFormviewField()
    {
        return $this->formViewField;
    }

    /**
     * @return FormViewNode
     */
    public function setFormViewField(FormViewField $formViewField = null)
    {
        $this->formViewField = $formViewField;

        return $this;
    }

    /**
     * @return FormViewNode
     */
    public function removeChild(FormViewNode $child)
    {
        $this->children->removeElement($child);

        return $this;
    }

    /**
     * @return FormViewNode
     */
    public function removeChildren()
    {
        $this->children->clear();

        return $this;
    }

    /**
     * Returns the field mapped by a given id.
     *
     * @return mixed
     * @throws DefinitionNotFoundException
     */
    public function getChild($id)
    {
        foreach ($this->children() as $child) {
            if ($id instanceof FormViewNode && $id === $child) {
                return $child;
            } elseif ($child->getId() == $id) {
                return $child;
            }
        }

        if ($id instanceof FormViewNode) {
            $id = $id->getName();
        }

        throw new DefinitionNotFoundException($id);
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return boolean
     */
    public function hasChild($id)
    {
        try {
            $this->getChild($id);

            return true;
        } catch (DefinitionNotFoundException $e) {
            return false;
        }
    }

    /**
     * @return FormViewNode
     */
    public function addChild(FormViewNode $child)
    {
        $this->children->add($child);

        return $this;
    }

    /**
     * @return FormViewNode
     */
    public function createNode($name = null, $type = null, $sequence = null, FormViewField $formViewField = null)
    {
        $formViewNode = self::create($name, $type, $sequence, $formViewField);
        $this->addChild($node);

        return $formViewNode;
    }
}
