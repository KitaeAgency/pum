<?php

namespace Pum\Core\Definition\View;

use Pum\Core\Definition\FieldDefinition;

class TableViewField extends AbstractViewField
{
    const DEFAULT_VIEW = 'default';

    /**
     * @var TableView
     */
    protected $tableview;

    /**
     * @var string
     */
    protected $view;

    /**
     * @var integer
     */
    protected $sequence;

    /**
     * Constructor.
     */
    public function __construct($label = null, FieldDefinition $fieldDefinition = null, $view = self::DEFAULT_VIEW, $sequence = null)
    {
        $this->label           = $label;
        $this->fieldDefinition = $fieldDefinition;
        $this->view            = $view;
        $this->sequence        = $sequence;
    }

    /**
     * @return ObjectDefinition
     */
    public static function create($label = null, FieldDefinition $fieldDefinition = null, $view = self::DEFAULT_VIEW, $sequence = null)
    {
        return new self($label, $fieldDefinition, $view, $sequence);
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
     * @return TableViewField
     */
    public function setTableview(Tableview $tableview = null)
    {
        $this->tableview = $tableview;

        return $this;
    }

    /**
     * @return FieldDefinition
     */
    public function getFieldDefinition()
    {
        return $this->fieldDefinition;
    }

    /**
     * Changes associated fieldDefinition.
     *
     * @return TableViewField
     */
    public function setFieldDefinition(FieldDefinition $fieldDefinition = null)
    {
        $this->fieldDefinition = $fieldDefinition;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return TableViewField
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return TableViewField
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return integer
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @return TableViewField
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }
}
