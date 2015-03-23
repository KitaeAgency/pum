<?php

namespace Pum\Core\Definition\View;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Exception\DefinitionNotFoundException;

class ObjectViewNode extends AbstractViewNode
{
    /**
     * @var ObjectViewField
     */
    protected $objectViewField;

    /**
     * Constructor.
     */
    public function __construct($name = null, $type = null, $sequence = null, ObjectViewField $objectViewField = null)
    {
        $this->name            = $name;
        $this->sequence        = $sequence;
        $this->objectViewField = $objectViewField;
        $this->options         = array();
        $this->children        =  new ArrayCollection();

        $this->setType($type);
    }

    /**
     * @return ObjectViewNode
     */
    public static function create($name = null, $type = null, $sequence = null, ObjectViewField $objectViewField = null)
    {
        return new self($name, $type, $sequence, $objectViewField);
    }

    /**
     * @return ObjectViewNode
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return ObjectViewNode
     */
    public function setParent(ObjectViewNode $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return ObjectViewField
     */
    public function getObjectViewField()
    {
        return $this->objectViewField;
    }

    /**
     * @return ObjectViewNode
     */
    public function setObjectViewField(ObjectViewField $objectViewField = null)
    {
        $this->objectViewField = $objectViewField;

        return $this;
    }

    /**
     * @return ObjectViewNode
     */
    public function removeChild(ObjectViewNode $child)
    {
        $this->children->removeElement($child);

        return $this;
    }

    /**
     * @return ObjectViewNode
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
        foreach ($this->children as $child) {
            if ($id instanceof ObjectViewNode && $id === $child) {
                return $child;
            } elseif ($child->getId() == $id) {
                return $child;
            }
        }

        if ($id instanceof ObjectViewNode) {
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
     * @return ObjectViewNode
     */
    public function addChild(ObjectViewNode $child)
    {
        $this->children->add($child);

        return $this;
    }

    /**
     * @return ObjectViewNode
     */
    public function createNode($name = null, $type = null, $sequence = null, objectViewField $objectViewField = null)
    {
        $objectViewNode = self::create($name, $type, $sequence, $objectViewField);

        $objectViewNode->setParent($this);
        $this->addChild($objectViewNode);

        return $objectViewNode;
    }

    /**
     * @return String
     */
    public function getChildType($returnObjectViewField = false)
    {
        foreach ($this->children as $node) {
            if ($node::TYPE_FIELD === $node->getType() && null !== $node->getObjectViewField() && 'tab' == $node->getObjectViewField()->getOption('form_type')) {
                if ($returnObjectViewField) {
                    return $node->getObjectViewField();
                }

                return 'relationFields';
            }

            break;
        }

        return 'regularFields';
    }

    /**
     * @return Array
     */
    public function getObjectViewFields(&$children = array())
    {
        foreach ($this->children as $child) {
            if (null !== $child->getObjectViewField()) {
                $children[] = $child->getObjectViewField();
            }

            $child->getObjectViewFields($children);
        }

        return $children;
    }
}
