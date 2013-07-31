<?php

namespace Pum\Core\Type;

use Pum\Core\Exception\FeatureNotImplementedException;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Pum\Core\Object\Object;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

abstract class AbstractType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormOptionsType()
    {
        throw new FeatureNotImplementedException('getFormOptionsType for '.get_class($this));
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        throw new FeatureNotImplementedException('mapDoctrineFields for '.get_class($this));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        throw new FeatureNotImplementedException('buildForm for '.get_class($this));
    }

    /**
     * {@inheritdoc}
     */
    public function writeValue(Object $object, $value, $name, array $options)
    {
        $object->__pum__rawSet($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function readValue(Object $object, $name, array $options)
    {
        return $object->__pum__rawGet($name);
    }
}
