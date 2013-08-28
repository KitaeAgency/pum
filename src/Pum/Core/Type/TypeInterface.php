<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Pum\Core\Object\Object;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Interface for a field type.
 */
interface TypeInterface
{
    /**
     * Form used for configuration.
     *
     * @return string
     */
    public function buildOptionsForm(FormInterface $form);

    /**
     * Adds mapping informations to a Doctrine class metadata.
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options);

    /**
     * Adds fields to the form according to type and options.
     */
    public function buildForm(FormInterface $form, $name, array $options);

    /**
     * Adds validation rules to the metadata according to type and options.
     */
    public function mapValidation(ClassMetadata $metadata, $name, array $options);

    /**
     * Writes values to an object.
     */
    public function writeValue(Object $object, $value, $name, array $options);

    /**
     * Read value from an object.
     *
     * @return mixed
     */
    public function readValue(Object $object, $name, array $options);

    /**
     * Returns raw columns used by the data type.
     *
     * @return array
     */
    public function getRawColumns($name, array $options);
}
