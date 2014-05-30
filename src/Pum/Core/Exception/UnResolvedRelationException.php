<?php

namespace Pum\Core\Exception;

/**
 * Class UnResolvedRelationException
 * @package Pum\Core\Exception
 */
class UnResolvedRelationException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct(sprintf('Trying to access target object of an unresolved relation'));
    }
}
