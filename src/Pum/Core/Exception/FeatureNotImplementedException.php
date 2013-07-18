<?php

namespace Pum\Core\Exception;

class FeatureNotImplementedException extends \RuntimeException
{
    public function __construct($featureName, \Exception $previous = null)
    {
        parent::__construct(sprintf('Feature "%s" not implemented.', $featureName), null, $previous);
    }
}
