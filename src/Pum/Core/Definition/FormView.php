<?php

namespace Pum\Core\Definition;

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
    protected $rows;

    /**
     * @param ObjectDefinition $objectDefinition
     * @param string $name name of the form view.
     */
    public function __construct(ObjectDefinition $objectDefinition = null, $name = null)
    {
        $this->objectDefinition  = $objectDefinition;
        $this->name    = $name;
        $this->private = false;
        $this->rows  = array();
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
     * @return array an array of field names in the view.
     */
    public function getRowNames()
    {
        return array_keys($this->rows);
    }

    /**
     * @return FormView
     */
    public function removeRow($name)
    {
        if (isset($this->rows[$name])) {
            unset($this->rows[$name]);
        }

        return $this;
    }

    /**
     * @return FormView
     */
    public function removeRows()
    {
        $this->rows = array();

        return $this;
    }

    /**
     * Returns the field mapped by a given label.
     *
     * @param string $name
     *
     * @return string
     */
    public function getRowField($name)
    {
        if ($name === 'id') {
            return 'id';
        }

        if (!isset($this->rows[$name])) {
            throw new \InvalidArgumentException(sprintf('No row named "%s" in form view.', $name));
        }
        
        return $this->rows[$name][0];
    }

    public function hasRow($name)
    {
        return isset($this->rows[$name]);
    }

    /**
     * Returns the view for a given row label.
     *
     * @param string $name
     *
     * @return string
     */
    public function getRowView($name)
    {
        if (!isset($this->rows[$name])) {
            throw new \InvalidArgumentException(sprintf('No row named "%s" in form view.', $name));
        }

        return $this->rows[$name][1];
    }

    /**
     * @param string $name  name of the column
     * @param string $field field of object to display
     * @param string $view  the view block to use for rendering of field
     * @param boolean $show  the view block to use for rendering of field
     *
     * @return FormView
     */
    public function addRow($name, $field = null, $view = 'default')
    {
        if (null === $field) {
            $field = $name;
        }

        $this->rows[$name] = array($field, $view);

        return $this;
    }
}
