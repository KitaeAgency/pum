<?php

namespace Pum\Core\Definition;

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
    protected $columns;

    /**
     * @param ObjectDefinition $objectDefinition
     * @param string $name name of the object view.
     */
    public function __construct(ObjectDefinition $objectDefinition = null, $name = null)
    {
        $this->objectDefinition  = $objectDefinition;
        $this->name    = $name;
        $this->private = false;
        $this->columns = array();
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
     * @return array an array of column names in the view.
     */
    public function getColumnNames()
    {
        return array_keys($this->columns);
    }

    /**
     * @return ObjectView
     */
    public function removeColumn($name)
    {
        if (isset($this->columns[$name])) {
            unset($this->columns[$name]);
        }

        return $this;
    }

    /**
     * @return ObjectView
     */
    public function removeColumns()
    {
        $this->columns = array();

        return $this;
    }

    /**
     * Returns the column mapped by a given column.
     *
     * @param string $name
     *
     * @return string
     */
    public function getColumnField($name)
    {
        if ($name === 'id') {
            return 'id';
        }

        if (!isset($this->columns[$name])) {
            throw new \InvalidArgumentException(sprintf('No column named "%s" in object view.', $name));
        }

        return $this->columns[$name][0];
    }

    public function hasColumn($name)
    {
        return isset($this->columns[$name]);
    }

    /**
     * Returns the column view for a given column.
     *
     * @param string $name
     *
     * @return string
     */
    public function getColumnView($name)
    {
        if (!isset($this->columns[$name])) {
            throw new \InvalidArgumentException(sprintf('No column named "%s" in object view.', $name));
        }

        return $this->columns[$name][1];
    }

    /**
     * @param string $name  name of the column
     * @param string $field field of object to display
     * @param string $view  the view block to use for rendering of field
     * @param boolean $show  the view block to use for rendering of field
     *
     * @return ObjectView
     */
    public function addColumn($name, $field = null, $view = 'default')
    {
        if (null === $field) {
            $field = $name;
        }

        $this->columns[$name] = array($field, $view);

        return $this;
    }
}
