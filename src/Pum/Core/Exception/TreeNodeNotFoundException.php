<?php

namespace Pum\Core\Exception;

class TreeNodeNotFoundException extends \InvalidArgumentException
{
    public function __construct($id)
    {
        parent::__construct(sprintf('Tree node id "%s" not found.', $id));
    }
}
