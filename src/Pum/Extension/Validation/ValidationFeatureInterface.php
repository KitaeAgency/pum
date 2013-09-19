<?php

interface ValidationFeatureInterface
{
    /**
     * Adds validation rules to the metadata according to type and options.
     */
    public function mapValidation(ClassMetadata $metadata, $name, array $options);
}
