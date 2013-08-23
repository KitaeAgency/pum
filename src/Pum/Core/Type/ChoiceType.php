<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormInterface;

class ChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getFormOptionsType()
    {
        return 'ww_field_type_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $unique = isset($options['unique']) ? $options['unique'] : false;

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => 'text',
            'nullable'  => true,
            'unique'    => $unique,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $choices = isset($options['choices']) ? $options['choices'] : array();
        $form->add($name, 'choice', array(
            'choices'   => $choices,
            'empty_value' => 'Choose your '. $name,
       ));
    }
}
