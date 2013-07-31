<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormInterface;

class TextType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getFormOptionsType()
    {
        return 'ww_field_type_text';
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $multiLines = isset($options['multi_lines']) ? $options['multi_lines'] : false;
        $length     = isset($options['length']) ? $options['length'] : 100;
        $unique     = isset($options['unique']) ? $options['unique'] : false;

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => $multiLines ? 'text' : 'string',
            'length'    => $length,
            'nullable'  => true,
            'unique'    => $unique,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $form->add($name, 'text');
    }
}
