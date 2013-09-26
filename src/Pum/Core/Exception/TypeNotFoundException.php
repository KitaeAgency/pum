<?php

namespace Pum\Core\Exception;

class TypeNotFoundException extends \InvalidArgumentException
{
    public function __construct($name)
    {
        parent::__construct(sprintf('Type with name "%s" was not found.', $name));
    }
}
