<?php

namespace Pum\Core\Exception;

class BeamNotFoundException extends \InvalidArgumentException
{
    public function __construct($name, \Exception $previous = null)
    {
        parent::__construct(sprintf('Beam with name "%s" was not found.', $name), null, $previous);
    }
}
