<?php

namespace Pum\Core\Extension\Core\Type;

use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidationClassMetadata;

class BooleanType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'required'        => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('required', 'checkbox', array('required' => false))
        ;
    }

    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'name'      => $context->getField()->getLowercaseName(),
            'fieldName' => $context->getField()->getCamelCaseName(),
            'type'      => 'boolean',
            'nullable'  => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildField(FieldBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $name = $context->getField()->getCamelCaseName();

        $cb->createProperty($name);
        $cb->addGetMethod($name);
        $cb->addSetMethod($name);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FieldContext $context, FormInterface $form)
    {
        $form->add($context->getField()->getCamelCaseName(), 'checkbox');
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilterForm(FormBuilderInterface $builder)
    {
        $choicesKey = array('1', '0');
        $choicesValue = array('Yes', 'No');

        $builder
            ->add('value', 'choice', array(
                'choices'  => array_combine($choicesKey, $choicesValue)
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(FieldContext $context, ValidationClassMetadata $metadata)
    {
        $metadata->addGetterConstraint($context->getField()->getCamelCaseName(), new Type(array('type' => 'boolean')));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'boolean';
    }
}
