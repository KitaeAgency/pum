<?php

namespace Pum\Core\Extension\Validation;

use Pum\Core\Context\FieldContext;
use Symfony\Component\Validator\Mapping\ClassMetadata;

interface ValidationFeatureInterface
{
    /**
     * Adds validation rules to the metadata according to type and options.
     */
    public function mapValidation(FieldContext $context, ClassMetadata $metadata);
}
