<?php

namespace Pum\Core\Definition\View;

use Pum\Core\Definition\FieldDefinition;

class FormViewField extends AbstractViewField
{
    const DEFAULT_VIEW = 'default';

    /**
     * @var FormView
     */
    protected $formView;

    /**
     * Constructor.
     */
    public function __construct($label = null, FieldDefinition $field = null, $view = self::DEFAULT_VIEW, $sequence = null)
    {
        $this->label     = $label;
        $this->field     = $field;
        $this->view      = $view;
        $this->sequence  = $sequence;
    }

    /**
     * @return FormViewField
     */
    public static function create($label = null, FieldDefinition $field = null, $view = self::DEFAULT_VIEW, $sequence = null)
    {
        return new self($label, $field, $view, $sequence);
    }

    /**
     * @return FormView
     */
    public function getFormView()
    {
        return $this->formView;
    }

    /**
     * Changes associated formView.
     *
     * @return FormViewField
     */
    public function setFormView(FormView $formView = null)
    {
        $this->formView = $formView;

        return $this;
    }
}
