<?php

namespace Pum\Core\Type;

use Pum\Core\Exception\FeatureNotImplementedException;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Pum\Core\Object\Object;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

abstract class AbstractType implements TypeInterface
{
    public function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);

        return $resolver->resolve($options);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

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
    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        throw new FeatureNotImplementedException('mapValidation for '.get_class($this));
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
        $object->_pumRawSet($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function readValue(Object $object, $name, array $options)
    {
        return $object->_pumRawGet($name);
    }
}
