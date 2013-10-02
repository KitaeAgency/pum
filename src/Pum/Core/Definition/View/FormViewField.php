<?php

namespace Pum\Core\Definition\View;

use Pum\Core\Definition\FieldDefinition;

class FormViewField extends AbstractViewField
{
    const DEFAULT_VIEW = 'default';

    /**
     * @var String
     */
    protected $placeholder;

    /**
     * @var FormView
     */
    protected $formView;

    /**
     * Constructor.
     */
    public function __construct($label = null, FieldDefinition $field = null, $view = self::DEFAULT_VIEW, $sequence = null, $placeholder = null)
    {
        $this->label       = $label;
        $this->field       = $field;
        $this->view        = $view;
        $this->sequence    = $sequence;
        $this->placeholder = $placeholder;
    }

    /**
     * @return FormViewField
     */
    public static function create($label = null, FieldDefinition $field = null, $view = self::DEFAULT_VIEW, $sequence = null, $placeholder = null)
    {
        return new self($label, $field, $view, $sequence, $placeholder);
    }

    /**
     * @return String
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @return FormViewField
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;

        return $this;
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
