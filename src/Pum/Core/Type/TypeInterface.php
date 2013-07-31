<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Pum\Core\Object\Object;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Interface for a field type.
 */
interface TypeInterface
{
    /**
     * Returns the form type used to configure type options.
     *
     * @return string
     */
    public function getFormOptionsType();

    /**
     * Adds mapping informations to a Doctrine class metadata.
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options);

    /**
     * Adds fields to the form according to type and options.
     */
    public function buildForm(FormInterface $form, $name, array $options);

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
}
