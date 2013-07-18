<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Interface for a field type.
 */
interface TypeInterface
{
    public function mapDoctrineFields(ObjectClassMetadata $metadata, FieldDefinition $definition);

    /**
     * @return FormTypeInterface
     */
    public function getFormType(FieldDefinition $definition);

    public function loadValidationMetadata(FieldDefinition $definition, ClassMetadata $metadata);
}
