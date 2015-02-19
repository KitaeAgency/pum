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
     * @var String
     */
    protected $help;
    
    /**
     * @var Boolean
     */
    protected $disabled;

    /**
     * @var FormView
     */
    protected $formView;

    /**
     * Constructor.
     */
    public function __construct($label = null, FieldDefinition $field = null, $view = self::DEFAULT_VIEW, $sequence = 0, $placeholder = null, $help = null, $disabled = 0)
    {
        $this->label       = $label;
        $this->field       = $field;
        $this->view        = $view;
        $this->sequence    = $sequence;
        $this->placeholder = $placeholder;
        $this->help        = $help;
        $this->disabled    = $disabled;
    }

    /**
     * @return FormViewField
     */
    public static function create($label = null, FieldDefinition $field = null, $view = self::DEFAULT_VIEW, $sequence = 0, $placeholder = null, $help = null, $disabled = 0)
    {
        return new self($label, $field, $view, $sequence, $placeholder, $help, $disabled);
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
     * @return Boolean
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * @return FormViewField
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * @return String
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * @return FormViewField
     */
    public function setHelp($help)
    {
        $this->help = $help;

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
