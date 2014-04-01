<?php

namespace Pum\Core\Definition\View;

use Pum\Core\Definition\FieldDefinition;

class ObjectViewField extends AbstractViewField
{
    const DEFAULT_VIEW = 'default';

    /**
     * @var ObjectView
     */
    protected $objectView;

    /**
     * Constructor.
     */
    public function __construct($label = null, FieldDefinition $field = null, $view = self::DEFAULT_VIEW, $sequence = 0)
    {
        $this->label     = $label;
        $this->field     = $field;
        $this->view      = $view;
        $this->sequence  = $sequence;
    }

    /**
     * @return ObjectViewField
     */
    public static function create($label = null, FieldDefinition $field = null, $view = self::DEFAULT_VIEW, $sequence = 0)
    {
        return new self($label, $field, $view, $sequence);
    }

    /**
     * @return ObjectView
     */
    public function getObjectView()
    {
        return $this->objectView;
    }

    /**
     * Changes associated objectView.
     *
     * @return ObjectViewField
     */
    public function setObjectView(ObjectView $objectView = null)
    {
        $this->objectView = $objectView;

        return $this;
    }
}

