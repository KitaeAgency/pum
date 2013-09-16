<?php

namespace Pum\Core\Exception;

use Pum\Core\Definition\ObjectDefinition;

class ObjectViewNotFoundException extends \InvalidArgumentException
{
    public function __construct(ObjectDefinition $object, $name, \Exception $previous = null)
    {
        parent::__construct(sprintf('Object view with name "%s" was not found in object "%s".', $name, $object->getName()), null, $previous);
    }
}
