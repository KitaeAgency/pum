<?php

namespace Pum\Core\Type;

use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Range;

class IntegerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $unique = isset($options['unique']) ? $options['unique'] : false;

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => 'integer',
            'nullable'  => true,
            'unique'    => $unique,
        ));
    }

    public function getFormOptionsType()
    {
        return 'ww_field_type_integer';
    }

    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        if ($options['min'] || $options['max']) {
            $metadata->addGetterConstraint($name, new Range(array('min' => $options['min'], 'max' => $options['max'])));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $form->add($name, 'number');
    }
}
