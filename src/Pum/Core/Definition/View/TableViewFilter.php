<?php

namespace Pum\Core\Definition\View;

class TableViewFilter
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var TableView
     */
    protected $tableview;

    /**
     * @var TableViewField
     */
    protected $column;

    /**
     * @var array
     */
    protected $values;

    /**
     * Constructor.
     */
    public function __construct(TableViewField $column = null, $values = null)
    {
        $this->column = $column;
        $this->values = $values;
    }

    /**
     * @return TableViewFilter
     */
    public static function create(TableViewField $column = null, $values = null)
    {
        return new self($column, $values);
    }

    /**
     * @return Tableview
     */
    public function getTableview()
    {
        return $this->tableview;
    }

    /**
     * Changes associated tableview.
     *
     * @return TableViewFilter
     */
    public function setTableview(Tableview $tableview = null)
    {
        $this->tableview = $tableview;

        return $this;
    }

    /**
     * @return TableViewField
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return TableViewFilter
     */
    public function setColumn(TableViewField $column)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return TableViewFilter
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @return string
     */
    public function getColumnName()
    {
        return $this->column->getLabel();
    }
}
