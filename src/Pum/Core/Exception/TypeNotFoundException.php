<?php

namespace Pum\Core\Exception;

class TypeNotFoundException extends \InvalidArgumentException
{
    public function __construct($name, \Exception $previous = null)
    {
        parent::__construct(sprintf('Type with name "%s" was not found.', $name), null, $e);
        }
    }
}
