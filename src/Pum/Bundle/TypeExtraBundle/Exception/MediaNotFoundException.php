<?php

namespace Pum\Bundle\TypeExtraBundle\Exception;

class MediaNotFoundException extends \InvalidArgumentException
{
    public function __construct($path, \Exception $previous = null)
    {
        parent::__construct(sprintf('Media with path "%s" was not found.', $path), null, $previous);
    }
}
