<?php

namespace Pum\Core\Extension\Core\Type;

use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\View\FormViewField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidationClassMetadata;

class IntegerType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'min'             => null,
            'max'             => null,
            'unique'          => false,
            'required'        => false,
            'label'           => null,
            'placeholder'     => null
        ));
    }

    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('unique', 'checkbox', array('required' => false))
            ->add('min', 'number', array('required' => false))
            ->add('max', 'number', array('required' => false))
        ;
    }

    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName(),
            'fieldName' => $context->getField()->getCamelCaseName(),
            'type'      => 'integer',
            'nullable'  => true
        ));
    }

    public function buildField(FieldBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $name = $context->getField()->getCamelCaseName();

        $cb->createProperty($name);
        $cb->addGetMethod($name);
        $cb->addSetMethod($name);
    }

    public function buildForm(FieldContext $context, FormInterface $form, FormViewField $formViewField)
    {
        $form->add($context->getField()->getCamelCaseName(), 'number', array(
            'required' => false,
            'label'    => $formViewField->getLabel(),
            'attr'     => array(
                'placeholder' => $formViewField->getPlaceholder()
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilterForm(FormBuilderInterface $builder)
    {
        $filterTypes = array('=', '<', '<=', '<>', '>', '>=');
        $filterNames = array('equal', 'inferior', 'inferior or equal', 'different', 'superior', 'superior or equal');

        $builder
            ->add('type', 'choice', array(
                'choices'  => array_combine($filterTypes, $filterNames)
            ))
            ->add('value', 'text')
        ;
    }

    public function mapValidation(FieldContext $context, ValidationClassMetadata $metadata)
    {
        $name = $context->getField()->getCamelCaseName();
        $metadata->addGetterConstraint($name, new Type(array('type' => 'integer')));

        if ($options['required']) {
            $metadata->addGetterConstraint($name, new NotBlank());
        }

        if ($options['min'] || $options['max']) {
            $metadata->addGetterConstraint($name, new Range(array('min' => $options['min'], 'max' => $options['max'])));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'integer';
    }
}
