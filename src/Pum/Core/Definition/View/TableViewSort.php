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
    public function setColumn(TableViewField $column)
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
        $authorizedOrder = self::getOrderTypes();
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
        if (is_null($this->getColumn())) {
            return 'id';
        }

        return $this->column->getLabel();
    }

    /**
     * @return FieldDefinition
     */
    public function getField()
    {
        if (is_null($this->getColumn())) {
            return null;
        }

        return $this->getColumn()->getField();
    }

    /**
     * @return array
     */
    public static function getOrderTypesTransKeys()
    {
        return array('pa.form.tableview.default.sort.order.types.asc', 'pa.form.tableview.default.sort.order.types.desc');
    }

    /**
     * @return array
     */
    public static function getOrderTypes()
    {
        return array('asc', 'desc');
    }
}
