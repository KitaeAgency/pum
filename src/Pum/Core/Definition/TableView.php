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
    protected $object;

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
    public function __construct(ObjectDefinition $object, $name = null)
    {
        $this->object  = $object;
        $this->name    = null === $name ? $object : $name;
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
