<?php

namespace Pum\Core\Exception;

class ExtensionNotFoundException extends \InvalidArgumentException
{
    public function __construct($name, array $available = array())
    {
        parent::__construct(sprintf('Extension "%s" not found. Available: %s', $name, count($available) ? implode(', ', $available) : '*none*'));
    }
}
