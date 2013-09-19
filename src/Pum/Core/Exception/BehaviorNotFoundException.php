<?php

namespace Pum\Core\Exception;

class BehaviorNotFoundException extends \InvalidArgumentException
{
    public function __construct($name)
    {
        parent::__construct(sprintf('Behavior named "%s" not found.', $name));
    }
}
