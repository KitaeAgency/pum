<?php

namespace Pum\Core\Definition;

class TableView
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
     * @var array
     */
    protected $columns;

    /**
     * @param ObjectDefinition $objectDefinition
     * @param string $name name of the table view.
     */
    public function __construct(ObjectDefinition $objectDefinition = null, $name = null)
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
     * @return array an array of visible column names in the view.
     */
    public function getVisibleColumnNames()
    {
        $visibleColumns = array();
        foreach ($this->columns as $name => $column) {
            if ($this->getColumnShow($name)) {
                $visibleColumns[] = $name;
            }
        }

        return $visibleColumns;
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
     * Returns the column show for a given column.
     *
     * @param string $name
     *
     * @return boolean
     */
    public function getColumnShow($name)
    {
        if (!isset($this->columns[$name])) {
            throw new \InvalidArgumentException(sprintf('No column named "%s" in table view.', $name));
        }

        return $this->columns[$name][2];
    }

    /**
     * @param string $name  name of the column
     * @param string $field field of object to display
     * @param string $view  the view block to use for rendering of field
     * @param boolean $show  the view block to use for rendering of field
     *
     * @return TableView
     */
    public function addColumn($name, $field = null, $view = 'default', $show = true)
    {
        if (null === $field) {
            $field = $name;
        }

        $this->columns[$name] = array($field, $view, $show);

        return $this;
    }

    /**
     * @param array $fields
     * @param array $fields
     * @param array $shows
     * @param array $views
     * 
     * @return TableView
     */
    public function addColumns($names = array(), $fields = array(), $views = array(), $shows = array())
    {
        $this->columns = array();

        foreach ($names as $k => $name) {
            $this->addColumn(
                $name, 
                (isset($fields[$k]) && $fields[$k]) ? $fields[$k]          : null, 
                (isset($views[$k])  && $views[$k])  ? $views[$k]           : 'default', 
                (isset($shows[$k]))                 ? (boolean)$shows[$k]  : false
            );
        }

        return $this;
    }
}
