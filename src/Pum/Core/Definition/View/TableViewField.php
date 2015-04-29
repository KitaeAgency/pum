<?php

namespace Pum\Core\Definition\View;

use Pum\Core\Definition\FieldDefinition;
use Doctrine\Common\Collections\ArrayCollection;

class TableViewField extends AbstractViewField
{
    const DEFAULT_VIEW = 'tableview';

    /**
     * @var TableView
     */
    protected $tableview;

    /**
     * @var ArrayCollection
     */
    protected $filters;

    /**
     * Constructor.
     */
    public function __construct($label = null, FieldDefinition $field = null, $view = self::DEFAULT_VIEW, $sequence = 0)
    {
        $this->label    = $label;
        $this->field    = $field;
        $this->view     = $view;
        $this->sequence = $sequence;
        $this->filters  = new ArrayCollection();
    }

    /**
     * @return TableViewField
     */
    public static function create($label = null, FieldDefinition $field = null, $view = self::DEFAULT_VIEW, $sequence = 0)
    {
        return new self($label, $field, $view, $sequence);
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
     * @return ArrayCollection
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Removes all filters for TableViewField.
     *
     * @return TableViewField
     */
    public function removeAllFilters()
    {
        $this->filters->clear();

        return $this;
    }

    /**
     * Adds a filter to the TableViewField.
     *
     * @param TableViewFilter $filter filter to add.
     *
     * @return TableViewField
     */
    public function addFilter(TableViewFilter $filter)
    {
        $filter->setColumn($this);
        $this->filters->add($filter);

        return $this;
    }

    /**
     * Removes a filter to the TableViewField.
     *
     * @param TableViewFilter $filter filter to remove.
     *
     * @return TableViewField
     */
    public function removeFilter(TableViewFilter $filter)
    {
        $this->filters->removeElement($filter);

        return $this;
    }

    /**
     * Creates a filter on the TableViewField on the fly.
     *
     * @return TableViewField
     */
    public function createFilter($type = '=', $value = null)
    {
        $this->addFilter(new TableViewFilter(null, $type, $value));

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        if (!$this->view) {
            return self::DEFAULT_VIEW;
        }

        return $this->view;
    }
}
