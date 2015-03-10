<?php

namespace Pum\Core\Definition\View;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Exception\DefinitionNotFoundException;

class NodeView
{
    const TYPE_TAB   = 'TAB';
    const TYPE_GROUP = 'GROUP';
    const TYPE_FIELD = 'FIELD';

    public static $types = array(
        self::TYPE_TAB,
        self::TYPE_GROUP,
        self::TYPE_FIELD,
    );

    /**
     * @var int
     */
    protected $id;

    /**
     * @var String
     */
    protected $name;
    
    /**
     * @var String
     */
    protected $description;

    /**
     * @var String
     */
    protected $type;

    /**
     * @var int
     */
    protected $sequence;

    /**
     * @var Array
     */
    protected $options;

    /**
     * @var ArrayCollection
     */
    protected $children;

    /**
     * @var NodeView
     */
    protected $parent;

    /**
     * @var FormView
     */
    protected $formView;

    /**
     * @var FormViewField
     */
    protected $formViewField;

    /**
     * Constructor.
     */
    public function __construct($name = null, $description = null, $type = null, $sequence = null, FormViewField $formViewField = null)
    {
        $this->name          = $name;
        $this->description   = $description;
        $this->type          = $type;
        $this->formViewField = $formViewField;
        $this->options       = array();
        $this->children      =  new ArrayCollection();
    }

    /**
     * @return FormViewField
     */
    public static function create($name = null, $description = null, $type = null, $sequence = null, FormViewField $formViewField = null)
    {
        return new self($name, $description, $type, $sequence, $formViewField);
    }

    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return NodeView
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return String
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return NodeView
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return String
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return NodeView
     */
    public function setType($type)
    {
        if (!in_array($type, self::$$types)) {
            throw new \RuntimeException(sprintf('Unknow type, allowed types are : "%s"', implode(' ,', self::$$types)));
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @return NodeView
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * @return NodeView
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return NodeView
     */
    public function setParent(NodeView $parent)
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
     * @return NodeView
     */
    public function setFormViewField(FormViewField $formViewField)
    {
        $this->formViewField = $formViewField;

        return $this;
    }

    /**
     * @return FormView
     */
    public function getFormview()
    {
        return $this->formView;
    }

    /**
     * @return NodeView
     */
    public function setFormView(FormView $formView)
    {
        $this->formView = $formView;

        return $this;
    }

    /**
     * @return Hybrid
     */
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    /**
     * @return NodeView
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * @return Bool
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * @return NodeView
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * @return Array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return NodeView
     */
    public function removeChild(NodeView $child)
    {
        $this->children->removeElement($child);

        return $this;
    }

    /**
     * @return NodeView
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
            if ($id instanceof NodeView && $id === $child) {
                return $child;
            } elseif ($child->getId() == $id) {
                return $child;
            }
        }

        if ($id instanceof NodeView) {
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
            $this->getField($id);

            return true;
        } catch (DefinitionNotFoundException $e) {
            return false;
        }
    }

    /**
     * @return NdeView
     */
    public function addChild(NodeView $chidl)
    {
        $this->children->add($field);

        return $this;
    }
}
