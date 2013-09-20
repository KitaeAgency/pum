<?php

namespace Pum\Core\Definition\View;

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
}
