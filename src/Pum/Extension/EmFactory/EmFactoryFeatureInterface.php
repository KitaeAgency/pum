<?php

namespace Pum\Extension\EmFactory;

interface EmFactoryFeatureInterface
{
    /**
     * Adds mapping informations to a Doctrine class metadata.
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options);
}
