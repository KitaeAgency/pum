<?php

namespace Pum\Core\Extension\Core\Type;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\View\FormViewField;
use Pum\Core\Extension\Validation\Constraints\Decimal;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidationClassMetadata;

class DecimalType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'required'    => false,
            'unique'      => false,
            'precision'   => 18,
            'scale'       => 0,
            'label'       => null,
            'placeholder' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('unique', 'checkbox', array('required' => false))
            ->add('precision', 'number', array('required' => false))
            ->add('scale', 'number', array('label' => 'Decimal', 'required' => false))
            ->add('required', 'checkbox', array('required' => false))
        ;
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
    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName(),
            'fieldName' => $context->getField()->getCamelCaseName(),
            'type'      => 'decimal',
            'nullable'  => true,
            'unique'    => $context->getOption('unique'),
            'precision' => $context->getOption('precision'),
            'scale'     => $context->getOption('scale'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(FieldContext $context, ValidationClassMetadata $metadata)
    {
        $metadata->addGetterConstraint($context->getField()->getCamelCaseName(), new Decimal());
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FieldContext $context, FormInterface $form, FormViewField $formViewField)
    {
        $form->add($context->getField()->getCamelCaseName(), 'text', array(
            'label' => $formViewField->getLabel(),
            'attr'  => array(
                'placeholder' => $formViewField->getPlaceholder()
            ),
            'required' => $context->getOption('required')
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'decimal';
    }
}
