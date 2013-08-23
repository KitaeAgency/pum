<?php

namespace Pum\Core\Type;

use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pum\Core\Validator\Constraints\Decimal;

class DecimalType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $unique    = isset($options['unique']) ? $options['unique'] : false;
        $precision = isset($options['precision']) ? $options['precision'] : 18;
        $scale     = isset($options['scale']) ? $options['scale'] : 0;

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => 'decimal',
            'nullable'  => true,
            'unique'    => $unique,
            'precision' => $precision,
            'scale'     => $scale,
        ));
    }

    public function getFormOptionsType()
    {
        return 'ww_field_type_decimal';
    }

    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        $metadata->addGetterConstraint($name, new Decimal());
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $form->add($name, 'text');
    }
}
