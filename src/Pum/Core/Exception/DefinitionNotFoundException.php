<?php

namespace Pum\Core\Exception;

/**
 * Exception class when a definition is not found.
 */
class DefinitionNotFoundException extends \InvalidArgumentException
{
    /**
     * Constructor.
     *
     * @param string    $name      name of the not found definition
     * @param array     $available correct values
     * @param Exception $previous  the previous exception (for chaining)
     */
    public function __construct($name, array $available, \Exception $previous = null)
    {
        parent::__construct(sprintf('Definition named "%s" not found. Available: %s.', $name, implode(', ', $available)), 0, $previous);
    }
}
