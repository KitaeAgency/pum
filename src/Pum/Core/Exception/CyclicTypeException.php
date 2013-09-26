<?php

namespace Pum\Core\Exception;

class CyclicTypeException extends \RuntimeException
{
    public function __construct($name, array $chain)
    {
        parent::__construct(sprintf('Error while resolving type "%s": cycle detection in type loading: %s -> %s', $name, implode(' -> ', $chain), $name));
    }
}
