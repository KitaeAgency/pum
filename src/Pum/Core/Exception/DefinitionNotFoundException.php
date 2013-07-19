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
     * @param Exception $previous  the previous exception (for chaining)
     */
    public function __construct($name, \Exception $previous = null)
    {
        parent::__construct(sprintf('Definition named "%s" not found.', $name), 0, $previous);
    }
}
