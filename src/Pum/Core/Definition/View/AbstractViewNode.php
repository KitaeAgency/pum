<?php

namespace Pum\Core\Definition\View;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Exception\DefinitionNotFoundException;

abstract class AbstractViewNode
{
    const TYPE_ROOT         = 'ROOT';
    const TYPE_GROUP_TAB    = 'GROUP_TAB';
    const TYPE_TAB          = 'TAB';
    const TYPE_TEMPLATE_TAB = 'TEMPLATE_TAB';
    const TYPE_GROUP_FIELD  = 'GROUP_FIELD';
    const TYPE_FIELD        = 'FIELD';

    public static $types = array(
        self::TYPE_ROOT,
        self::TYPE_GROUP_TAB,
        self::TYPE_TAB,
        self::TYPE_TEMPLATE_TAB,
        self::TYPE_GROUP_FIELD,
        self::TYPE_FIELD,
    );

    public static $tabTypes = array(
        self::TYPE_TAB,
        self::TYPE_TEMPLATE_TAB,
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
     * @var ViewNode
     */
    protected $parent;

    /**
     * @return Int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return FormViewNode
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
     * @return FormViewNode
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
     * @return FormViewNode
     */
    public function setType($type)
    {
        if (!in_array($type, self::$types)) {
            throw new \RuntimeException(sprintf('Unknow type, allowed types are : "%s"', implode(' ,', self::$types)));
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
     * @return FormViewNode
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

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
     * @return FormViewNode
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
     * @return FormViewNode
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
     * @return FormViewNode
     */
    public function end()
    {
        return $this->getParent();
    }

    /**
     * @return Boolean
     */
    public function isRoot()
    {
        return self::TYPE_ROOT === $this->getType();
    }

    /**
     * @return Boolean
     */
    public function isGroupTab()
    {
        return self::TYPE_GROUP_TAB === $this->getType();
    }

    /**
     * @return Boolean
     */
    public function isTab()
    {
        return in_array($this->getType(), self::$tabTypes);
    }

    /**
     * @return Boolean
     */
    public function isTemplateTab()
    {
        return self::TYPE_TEMPLATE_TAB === $this->getType();
    }

    /**
     * @return Boolean
     */
    public function isGroupField()
    {
        return self::TYPE_GROUP_FIELD === $this->getType();
    }

    /**
     * @return Boolean
     */
    public function isField()
    {
        return self::TYPE_FIELD === $this->getType();
    }
}
