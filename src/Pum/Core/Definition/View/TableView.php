<?php

namespace Pum\Core\Definition\View;

use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Exception\DefinitionNotFoundException;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

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
     * @var ArrayCollection
     */
    protected $columns;

    /**
     * @var ArrayCollection
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
        $this->columns = new ArrayCollection();
        $this->filters = new ArrayCollection();
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
     * @return ArrayCollection
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Tests if tableview has a column with given label.
     *
     * @param string $label label of tableViewField
     *
     * @return boolean
     */
    public function hasColumn($label)
    {
        foreach ($this->columns as $column) {
            if ($column->getLabel() == $label) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return TableViewField
     *
     * @throws TableViewFieldNotFoundException
     */
    public function getColumn($label)
    {
        foreach ($this->getColumns() as $column) {
            if ($column->getLabel() === $label) {
                return $column;
            }
        }

        throw new DefinitionNotFoundException($label);
    }

    /**
     * Adds a column to the tableview.
     *
     * @param TableViewField $column column to add.
     *
     * @return TableView
     */
    public function addColumn(TableViewField $column)
    {
        $column->setTableview($this);
        $this->columns->add($column);

        return $this;
    }

    /**
     * Removes a column to the tableview.
     *
     * @param TableViewField $column column to remove.
     *
     * @return TableView
     */
    public function removeColumn(TableViewField $column)
    {
        $this->columns->removeElement($column);

        return $this;
    }

    /**
     * Creates a column on the tableview on the fly.
     *
     * @return tableview
     */
    public function createColumn($label, FieldDefinition $field = null, $view = TableViewField::DEFAULT_VIEW, $sequence = null)
    {
        if ($this->hasColumn($label)) {
            throw new \RuntimeException(sprintf('Column "%s" is already present in tableview "%s".', $label, $this->name));
        }

        $this->addColumn(new TableViewField($label, $field, $view, $sequence));

        return $this;
    }

    /**
     * Takes an array of values, indexed by 0, 1, 2... and returns
     * an array with associative key being column names.
     *
     * @param array $values
     *
     * @return array $values
     */
    public function combineValues(array $values)
    {
        $result = array();

        $columnNames = $this->getColumnNames();
        foreach ($values as $k => $value) {
            if (!isset($columnNames[$k])) {
                throw new \InvalidArgumentException(sprintf('No column indexed "%s" in table view.', $k));
            }
            $result[$columnNames[$k]] = $value;
            $k++;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Removes all filters from the table view.
     *
     * @return TableView
     */
    public function removeFilter($name)
    {
        if (isset($this->filters[$name])) {
            unset($this->filters[$name]);
        }

        return $this;
    }

    /**
     * Returns the filter value for a given column.
     *
     * @param string $name
     *
     * @return string
     */
    public function getFilterValue($name)
    {
        if (!isset($this->filters[$name])) {
            return null;
        }

        return $this->filters[$name];
    }

    /**
     * @param string $column the column of the filter
     * @param string $value  the value of the filter
     * @param string $type   the type pf the filter [=, <, <=, <>, >, >=, !=, LIKE]
     *
     * @return TableView
     */
    public function addFilter($column, $values)
    {
        $this->filters[$column] = $values;

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
     * Returns the default sort column.
     *
     * @return string
     */
    public function getDefaultSortColumn()
    {
        return (isset($this->defaultSort['column'])) ? $this->defaultSort['column'] : 'id';
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
     * Returns the default sort order.
     *
     * @return string
     */
    public function getDefaultSortOrder()
    {
        return (isset($this->defaultSort['order'])) ? $this->defaultSort['order'] : 'asc';
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
     * Removes default sort from the table view.
     *
     * @return TableView
     */
    public function removeDefaultSort()
    {
        $this->defaultSort = array('column' => 'id', 'order' => 'asc');

        return $this;
    }
}
