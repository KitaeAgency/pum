<?php

namespace Pum\Core\Exception;

class ClassNotFoundException extends \RuntimeException
{
    public function __construct($class)
    {
        parent::__construct(sprintf('Class "%s" was not found.', $class));
    }
}
