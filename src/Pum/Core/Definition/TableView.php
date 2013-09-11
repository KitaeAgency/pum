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
    protected $defaultSort;

    /**
     * @param ObjectDefinition $objectDefinition
     * @param string $name name of the table view.
     */
    public function __construct(ObjectDefinition $objectDefinition = null, $name = null)
    {
        $this->objectDefinition  = $objectDefinition;
        $this->name    = $name;
        $this->columns = array();
        $this->defaultSort = array();
        $this->private = false;
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
     * Returns the default sort column.
     *
     * @return string
     */
    public function getDefaultSortColumn()
    {
        return (isset($this->defaultSort['column'])) ? $this->defaultSort['column'] : '';
    }

    /**
     * Returns the default sort order.
     *
     * @return string
     */
    public function getDefaultSortOrder()
    {
        return (isset($this->defaultSort['order'])) ? $this->defaultSort['order'] : '';
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
     * @param string $defaultSortColumn column for the sort
     * @param string $defaultSortOrder order type
     *
     * @return TableView
     */
    public function setDefaultSort($defaultSortColumn = 'id', $defaultSortOrder = 'asc')
    {
        /* TODO : SORT BY COLUMN, CURRENTLY WE ONLY SORT BY FIELD */
        if (!$this->objectDefinition->hasField($defaultSortColumn) && $defaultSortColumn !== 'id') {
            throw new \InvalidArgumentException(sprintf('No field named "%s" in objectDefinition "%s" for default sort.', $defaultSortColumn, $objectDefinition->getName()));
        }

        $defaultSortOrder = ($defaultSortOrder) ?: 'asc';
        $authorizedOrder = array('asc', 'desc');
        if (!in_array(strtolower($defaultSortOrder), $authorizedOrder)) {
            throw new \InvalidArgumentException(sprintf('Unauthorized order "%s". Authorized order are "%s".', $defaultSortOrder, implode(', ', $authorizedOrder)));
        }

        $this->defaultSort['column'] = $defaultSortColumn;
        $this->defaultSort['order']  = strtolower($defaultSortOrder);

        return $this;
    }

    /**
     * @param array $names
     * @param array $fields
     * @param array $views
     * @param array $shows
     * @param array $orders
     * @param string $defaultSortColumn
     * @param string $defaultSortOrder
     * @param boolean $isPrivate
     * 
     * @return TableView
     */
    public function configure(Request $request)
    {
        $names  = $request->request->get('columns[names]', array(), true);
        $fields = $request->request->get('columns[fields]', array(), true);
        $views  =  $request->request->get('columns[views]',  array(), true);
        $shows  = $request->request->get('columns[shows]',  array(), true);
        $orders = $request->request->get('columns[orders]',  array(), true);

        $filtersColumns = $request->request->get('filters[columns]',  array(), true);
        $filtersValues  = $request->request->get('filters[values]',  array(), true);

        $defaultSortColumn = $request->request->get('defaultSortColumn');
        $defaultSortOrder  = $request->request->get('defaultSortOrder');

        $isPrivate         = $request->request->get('is_private',  false);


        $this->columns = array();
        $this->defaultSort = array();

        asort($orders);

        foreach ($orders as $k => $order) {
            if (isset($names[$k]) && $names[$k]) {
                $name = $names[$k];
            } elseif (isset($fields[$k]) && $fields[$k]){
                $name = $fields[$k];
            } else {
                throw new \InvalidArgumentException(sprintf('Unvalid form : Cannot resolve column name'));
            }

            $this->addColumn(
                $name,
                (isset($fields[$k]) && $fields[$k]) ? $fields[$k]          : null, 
                (isset($views[$k])  && $views[$k])  ? $views[$k]           : 'default', 
                (isset($shows[$k]))                 ? (boolean)$shows[$k]  : false
            );
        }

        if ($defaultSortColumn) {
            $this->setDefaultSort($defaultSortColumn, $defaultSortOrder);
        }

        $this->setPrivate($isPrivate);

        return $this;
    }
}
