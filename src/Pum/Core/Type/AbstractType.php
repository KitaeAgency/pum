<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Doctrine\Metadata\ObjectClassMetadata;
use Pum\Core\Exception\FeatureNotImplementedException;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

abstract class AbstractType
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
    public function getFormType(FieldDefinition $definition)
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
}
