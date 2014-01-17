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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidationClassMetadata;

class TextType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'max_length'  => null,
            'min_length'  => null,
            'multilines'  => true,
            'unique'      => false,
            'required'    => false,
            'label'       => null,
            'placeholder' => null
        ));
    }

    public function buildForm(FieldContext $context, FormInterface $form, FormViewField $formViewField)
    {
        $textType = $context->getOption('multilines') ? 'textarea' : 'text';

        $form->add($context->getField()->getCamelCaseName(), $textType, array(
            'label' => $formViewField->getLabel(),
            'attr'  => array(
                'placeholder' => $formViewField->getPlaceholder()
            ),
            'required' => $context->getOption('required')
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

    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName(),
            'fieldName' => $context->getField()->getCamelCaseName(),
            'type'      => $context->getOption('max_length') ? 'string' : 'text',
            'length'    => $context->getOption('max_length'),
            'unique'    => $context->getOption('unique'),
            'nullable'  => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('max_length', 'number', array('required' => false))
            ->add('min_length', 'number', array('required' => false))
            ->add('multilines', 'checkbox', array('required' => false))
            ->add('unique', 'checkbox', array('required' => false))
            ->add('required', 'checkbox', array('required' => false))
        ;
    }

     /**
    * {@inheritdoc}
    */
    public function buildFilterForm(FormBuilderInterface $builder)
    {
        $filterTypes = array('=', '<>', 'LIKE', 'BEGIN', 'END');
        $filterNames = array('pa.form.tableview.columns.entry.filters.entry.type.types.equal', 'pa.form.tableview.columns.entry.filters.entry.type.types.different', 'pa.form.tableview.columns.entry.filters.entry.type.types.contains', 'pa.form.tableview.columns.entry.filters.entry.type.types.starting_with', 'pa.form.tableview.columns.entry.filters.entry.type.types.ending_with');

        $builder
            ->add('type', 'choice', array(
                'choices' => array_combine($filterTypes, $filterNames)
            ))
            ->add('value', 'text')
        ;
    }

    public function mapValidation(FieldContext $context, ValidationClassMetadata $metadata)
    {
        $maxLength  = $context->getOption('max_length');
        $minLength  = $context->getOption('min_length');
        $required   = $context->getOption('required');

        if ($maxLength || $minLength) {
            $constraint = new Length(array('min' => $minLength, 'max' => $maxLength));
            $metadata->addGetterConstraint($context->getField()->getCamelCaseName(), $constraint);
        }

        if ($required) {
            $constraint = new NotBlank();
            $metadata->addGetterConstraint($context->getField()->getCamelCaseName(), $constraint);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'text';
    }
}
