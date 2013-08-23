<?php

namespace Pum\Core\Type;

use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pum\Core\Validator\Constraints\DateTime as DateTimeConstraints;

class DatetimeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $unique    = isset($options['unique']) ? $options['unique'] : false;

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => 'datetime',
            'nullable'  => true,
            'unique'    => $unique,
        ));
    }

    public function getFormOptionsType()
    {
        return 'ww_field_type_datetime';
    }

    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        $metadata->addGetterConstraint($name, new DateTimeConstraints(array('restriction' => $options['restriction'])));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $form->add($name, 'datetime');
    }
}
