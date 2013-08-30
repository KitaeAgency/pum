<?php

namespace Pum\Core\Exception;

use Pum\Core\Definition\ObjectDefinition;

class TableViewNotFoundException extends \InvalidArgumentException
{
    public function __construct(ObjectDefinition $object, $name, \Exception $previous = null)
    {
        parent::__construct(sprintf('Table view with name "%s" was not found in object "%s".', $name, $object->getName()), null, $previous);
    }
}
