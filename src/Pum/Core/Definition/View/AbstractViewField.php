<?php

namespace Pum\Core\Definition\View;

use Pum\Core\Definition\FieldDefinition;

abstract class AbstractViewField
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var FieldDefinition
     */
    protected $fieldDefinition;

    /**
     * @var string
     */
    protected $view;

    /**
     * @var integer
     */
    protected $sequence;

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
