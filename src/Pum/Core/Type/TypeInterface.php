<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Pum\Core\Object\Object;
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
    public function getFormOptionsType();

    public function loadValidationMetadata(FieldDefinition $definition, ClassMetadata $metadata);

    public function writeValue(Object $object, $name, $value);
    public function readValue(Object $object, $name);
}
