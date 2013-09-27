<?php

namespace Pum\Core\Definition\View;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\View\TableViewField;
use Pum\Core\Exception\DefinitionNotFoundException;

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
     * @var TableViewSort
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
    public function createColumn($label, $field = null, $view = TableViewField::DEFAULT_VIEW, $sequence = 1)
    {
        if (null === $field) {
            $field = $label;
        }

        if (is_string($field) && $this->getObjectDefinition()) {
            $field = $this->getObjectDefinition()->getField($field);
        }

        if (!$field instanceof FieldDefinition) {
            throw new \InvalidArgumentException(sprintf('Expected a FieldDefinition got a "%s".', is_object($field) ? get_class($field ) : gettype($field)));
        }

        if ($this->hasColumn($label)) {
            throw new \RuntimeException(sprintf('Column "%s" is already present in tableview "%s".', $label, $this->name));
        }

        $this->addColumn(new TableViewField($label, $field, $view, $sequence));

        return $this;
    }

    /**
     * Returns the default sort.
     *
     * @return TableViewSort
     */
    public function getDefaultSort()
    {
        if (is_null($this->defaultSort)) {
            $this->defaultSort = new TableViewSort();
            $this->defaultSort->setTableView($this);
        }

        return $this->defaultSort;
    }

    /**
     * @return TableView
     */
    public function setDefaultSort(TableViewSort $defaultSort = null)
    {
        $defaultSort->setTableview($this);
        $this->defaultSort = $defaultSort;

        return $this;
    }

    /**
     * Returns the default sort columnName.
     *
     * @return string
     */
    public function getSortColumnName($name)
    {
        if (!is_null($name)) {
            return $name;
        }

        return $this->getDefaultSort()->getColumnName();
    }

    /**
     * Returns the sort field.
     *
     * @return FieldDefinition
     */
    public function getSortField($columnName)
    {
        if (is_null($columnName)) {
            return $this->getDefaultSort()->getField();
        }

        return $this->getColumn($columnName)->getField();
    }

    /**
     * Returns the sort order.
     *
     * @return string
     */
    public function getSortOrder($order)
    {
        if (!is_null($order)) {
            return $order;
        }

        return $this->getDefaultSort()->getOrder();
    }

    public function getColumnLabels()
    {
        return array_map(function (TableViewField $column) {
            return $column->getLabel();
        }, $this->getColumns()->toArray());
    }

    /**
     * Takes an array of values, indexed by 0, 1, 2... and returns
     * mixed array with filters data
     *
     * @param array $values
     *
     * @return mixed $values
     */
    public function combineValues(array $values)
    {
        $result = array();

        foreach ($this->getColumns() as $column) {
            $column->removeAllFilters();
        }

        $columnNames = $this->getColumnLabels();

        foreach ($values as $k => $filters) {
            if (!isset($columnNames[$k])) {
                throw new \InvalidArgumentException(sprintf('No column indexed "%s" in table view.', $k));
            }

            $column = $this->getColumn($columnNames[$k]);
            foreach ($filters as $filter) {
                if (isset($filter['type']) && isset($filter['value'])) {
                    $column->createFilter($filter['type'], $filter['value']);
                }
            }
        }

        return $this->getFilters();
    }

    /**
     * Return filters for columns
     *
     * @return mixed $values
     */
    public function getFilters()
    {
        $filters = array();
        $k = 0;
        foreach ($this->getColumns() as $column) {
            if (count($column->getFilters())) {
                $filters[] = array(
                    'key'     => $k,
                    'field'   => $column->getField(),
                    'filters' => $column->getFilters()
                );
            }
            $k++;
        }

        return $filters;
    }
}
