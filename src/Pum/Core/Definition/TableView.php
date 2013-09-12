<?php

namespace Pum\Core\Definition;

use Symfony\Component\HttpFoundation\Request;

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
     * @var boolean
     */
    protected $private;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var array
     */
    protected $filters;

    /**
     * @var array
     */
    protected $defaultSort;

    /**
     * @param ObjectDefinition $objectDefinition
     * @param string $name name of the table view.
     */
    public function __construct(ObjectDefinition $objectDefinition = null, $name = null)
    {
        $this->objectDefinition  = $objectDefinition;
        $this->name    = $name;
        $this->private = false;
        $this->columns = array();
        $this->filters = array();
        $this->defaultSort = array('column' => 'id', 'order' => 'asc');
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
     * @return TableView
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
     * @return TableView
     */
    public function setPrivate($private)
    {
        $this->private = (boolean)$private;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return array an array of column names in the view.
     */
    public function getColumnNames()
    {
        return array_keys($this->columns);
    }

    /**
     * @return TableView
     */
    public function removeColumn($name)
    {
        if (isset($this->columns[$name])) {
            unset($this->columns[$name]);
        }

        return $this;
    }

    /**
     * @return TableView
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
            throw new \InvalidArgumentException(sprintf('No column named "%s" in table view.', $name));
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
            throw new \InvalidArgumentException(sprintf('No column named "%s" in table view.', $name));
        }

        return $this->columns[$name][1];
    }

    /**
     * Returns the default sort column.
     *
     * @return string
     */
    public function getDefaultSortColumn()
    {
        return (isset($this->defaultSort['column'])) ? $this->defaultSort['column'] : null;
    }

    /**
     * Returns the default sort order.
     *
     * @return string
     */
    public function getDefaultSortOrder()
    {
        return (isset($this->defaultSort['order'])) ? $this->defaultSort['order'] : null;
    }

    /**
     * @param string $name  name of the column
     * @param string $field field of object to display
     * @param string $view  the view block to use for rendering of field
     * @param boolean $show  the view block to use for rendering of field
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

    /**
     * Removes all filters from the table view.
     *
     * @return TableView
     */
    public function removeFilters()
    {
        $this->filters = array();

        return $this;
    }

    /**
     * @param string $column the column of the filter
     * @param string $value  the value of the filter
     * @param string $type   the type pf the filter [=, <, <=, <>, >, >=, !=, LIKE]
     *
     * @return TableView
     */
    public function addFilter($column, $value, $type = '=')
    {
        $this->filters[] = array(
            'column' => $column,
            'value'  => $value,
            'type'   => $type
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getFilterTypes()
    {
        return array('=', '<', '<=', '<>', '>', '>=', '!=', 'LIKE');
    }

    /**
     * @param string $defaultSortColumn column for the sort
     * @param string $defaultSortOrder order type
     *
     * @return TableView
     */
    public function setDefaultSort($defaultSortColumn = 'id', $defaultSortOrder = 'asc')
    {
        return $this
            ->setDefaultSortColumn($defaultSortColumn)
            ->setDefaultSortOrder($defaultSortOrder)
        ;
    }

    /**
     * @return TableView
     */
    public function setDefaultSortColumn($column = 'id')
    {
        if (!$this->hasColumn($column) && $column !== 'id') {
            throw new \InvalidArgumentException(sprintf('No column named "%s" in table view. Available are: %s".', $column, implode(', ', $this->getColumnNames())));
        }

        $this->defaultSort['column'] = $column;

        return $this;
    }

    /**
     * @return TableView
     */
    public function setDefaultSortOrder($defaultSortOrder = 'asc')
    {
        $authorizedOrder = array('asc', 'desc');
        if (!in_array(strtolower($defaultSortOrder), $authorizedOrder)) {
            throw new \InvalidArgumentException(sprintf('Unauthorized order "%s". Authorized order are "%s".', $defaultSortOrder, implode(', ', $authorizedOrder)));
        }

        $this->defaultSort['order'] = $defaultSortOrder;

        return $this;
    }
}
