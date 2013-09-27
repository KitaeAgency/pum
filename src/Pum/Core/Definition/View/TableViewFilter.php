<?php

namespace Pum\Core\Definition\View;

class TableViewFilter
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var TableViewField
     */
    protected $column;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $value;

    /**
     * Constructor.
     */
    public function __construct(TableViewField $column = null, $type = '=', $value = null)
    {
        $this->column = $column;
        $this->type   = $type;
        $this->value  = $value;
    }

    /**
     * @return TableViewFilter
     */
    public static function create(TableViewField $column = null, $type = '=', $value = null)
    {
        return new self($column, $type, $value);
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return TableViewFilter
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return TableViewFilter
     */
    public function setValue($value)
    {
        $this->value = $value;

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
