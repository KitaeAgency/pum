<?php

namespace Pum\Core\Definition;

class TableView
{
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
     * @var array
     */
    protected $columns;

    /**
     * @param Beam $beam
     * @param string $name name of the table view.
     */
    public function __construct(ObjectDefinition $objectDefinition, $name = null)
    {
        $this->objectDefinition  = $objectDefinition;
        $this->name    = $name;
        $this->columns = array();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return TableView
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * Returns the column mapped by a given column.
     *
     * @param string $name
     *
     * @return string
     */
    public function getColumnField($name)
    {
        if (!isset($this->columns[$name])) {
            throw new \InvalidArgumentException(sprintf('No column named "%s" in table view.', $name));
        }

        return $this->columns[$name][0];
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
            throw new \InvalidArgumentException(sprintf('No column named "%s" in table view.', $name));
        }

        return $this->columns[$name][1];
    }

    /**
     * @param string $name  name of the column
     * @param string $field field of object to display
     * @param string $view  the view block to use for rendering of field
     *
     * @return TableView
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
