<?php

namespace Pum\Core\Event;

use Pum\Core\Definition\FieldDefinition;

/**
 * Object used for events related to fieldDefinition.
 *
 * @see Pum\Core\Events
 */
class FieldDefinitionEvent extends Event
{
    protected $fieldDefinition;

    public function __construct(FieldDefinition $fieldDefinition)
    {
        $this->fieldDefinition    = $fieldDefinition;
    }

    public function getFieldDefinition()
    {
        return $this->fieldDefinition;
    }
}
