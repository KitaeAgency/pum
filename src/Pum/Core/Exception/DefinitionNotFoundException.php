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
    public function __construct($projectName, $name, \Exception $previous = null)
    {
        parent::__construct(sprintf('Definition named "%s" in project "%s" not found.', $name, $projectName), 0, $previous);
    }
}
