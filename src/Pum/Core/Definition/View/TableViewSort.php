<?php

namespace Pum\Core\Definition\View;

class TableViewSort
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
     * @var string
     */
    protected $order;

    /**
     * Constructor.
     */
    public function __construct(TableViewField $column = null, $order = 'asc')
    {
        $this->column = $column;
        $this->order  = $order;
    }

    /**
     * @return TableViewSort
     */
    public static function create(TableViewField $column = null, $order = 'asc')
    {
        return new self($column, $order);
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
     * @return TableViewSort
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
     * @return TableViewSort
     */
    public function setColumn($column)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return TableViewSort
     */
    public function setOrder($order)
    {
        $authorizedOrder = array('asc', 'desc');
        if (!in_array($order = strtolower($order), $authorizedOrder)) {
            throw new \InvalidArgumentException(sprintf('Unauthorized order "%s". Authorized order are "%s".', $order, implode(', ', $authorizedOrder)));
        }
        
        $this->order = $order;

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
