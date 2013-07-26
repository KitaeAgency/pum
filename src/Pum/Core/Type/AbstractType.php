<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Exception\FeatureNotImplementedException;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Pum\Core\Extension\EmFactory\Object\Object;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

abstract class AbstractType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, FieldDefinition $definition)
    {
        throw new FeatureNotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptionsType()
    {
        throw new FeatureNotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function loadValidationMetadata(FieldDefinition $definition, ClassMetadata $metadata)
    {
        throw new FeatureNotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function writeValue(Object $object, $name, $value)
    {
        $object->__pum__rawSet($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function readValue(Object $object, $name)
    {
        return $object->__pum__rawGet($name);
    }
}
