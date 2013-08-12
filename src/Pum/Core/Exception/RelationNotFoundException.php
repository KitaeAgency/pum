<?php

namespace Pum\Core\Exception;

class RelationNotFoundException extends \InvalidArgumentException
{
    public function __construct($id, \Exception $previous = null)
    {
        parent::__construct(sprintf('Relation with id "%s" was not found.', $id), null, $previous);
    }
}
