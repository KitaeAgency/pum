<?php

namespace Pum\Core\Exception;

class DefinitionNotFoundException extends \InvalidArgumentException
{
    public function __construct($name)
    {
        parent::__construct(sprintf('Definition named "%s" not found.', $name));
    }
}
