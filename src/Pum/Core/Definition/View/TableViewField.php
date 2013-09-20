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
     * @return TableViewField
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
}
