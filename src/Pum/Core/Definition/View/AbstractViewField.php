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
    protected $field;

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return AbstractViewField
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return FieldDefinition
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Changes associated fieldDefinition.
     *
     * @return AbstractViewField
     */
    public function setField(FieldDefinition $field = null)
    {
        $this->field = $field;

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
     * @return AbstractViewField
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
     * @return AbstractViewField
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }
}
