<?php

namespace Pum\Extension\EmFactory;

use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\Definition\FieldDefinition;

interface EmFactoryFeatureInterface
{
    /**
     * Adds mapping informations to a Doctrine class metadata.
     */
    public function mapDoctrineField(FieldDefinition $field, ClassMetadata $metadata);
}
