<?php

namespace Pum\Core\Type;

use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pum\Core\Validator\Constraints\Date as DateConstraints;

class DateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $unique    = isset($options['unique']) ? $options['unique'] : false;

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => 'date',
            'nullable'  => true,
            'unique'    => $unique,
        ));
    }

    public function getFormOptionsType()
    {
        return 'ww_field_type_date';
    }

    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        $metadata->addGetterConstraint($name, new DateConstraints(array('restriction' => $options['restriction'])));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $form->add($name, 'date');
    }
}
