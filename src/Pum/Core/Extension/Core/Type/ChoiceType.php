<?php

namespace Pum\Core\Extension\Core\Type;

use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldContext;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidationClassMetadata;

class ChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'unique'  => false,
            'choices' => array()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('unique', 'checkbox', array('required' => false))
            ->add('choices', 'collection', array('type' => 'text', 'allow_add' => true, 'allow_delete' => true))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'fieldName' => $context->getField()->getCamelCaseName(),
            'type'      => 'text',
            'nullable'  => true,
            'unique'    => $context->getOption('unique'),
        ));
    }

    public function mapValidation(FieldContext $context, ValidationClassMetadata $metadata)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FieldContext $context, FormInterface $form)
    {
        $form
            ->add($name, 'choice', array(
                'choices'   => $context->getOption('choices'),
                'empty_value' => '-- Choose --',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'choice';
    }
}
