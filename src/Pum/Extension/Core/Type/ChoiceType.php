<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\QueryBuilder;

class ChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormInterface $form)
    {
        $form
            ->add('unique', 'checkbox', array('required' => false))
            ->add('choices', 'collection', array('type' => 'text', 'allow_add' => true, 'allow_delete' => true))
        ;
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

    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
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
