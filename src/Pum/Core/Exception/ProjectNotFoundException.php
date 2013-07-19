<?php

namespace Pum\Core\Exception;

class ProjectNotFoundException extends \InvalidArgumentException
{
    public function __construct($name, \Exception $previous = null)
    {
        parent::__construct(sprintf('Project with name "%s" was not found.', $name), null, $previous);
    }
}
