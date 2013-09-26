<?php

namespace Pum\Core\Extension\EmFactory;

use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\Context\FieldContext;

interface EmFactoryFeatureInterface
{
    /**
     * Adds mapping informations to a Doctrine class metadata.
     */
    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata);
}
