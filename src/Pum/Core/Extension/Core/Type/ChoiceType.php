<?php

namespace Pum\Core\Extension\Core\Type;

use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\View\FormViewField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
            'unique'      => false,
            'required'    => false,
            'choices'     => array(),
            'label'       => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('unique', 'checkbox', array('required' => false))
            ->add('required', 'checkbox', array('required' => false))
            ->add('choices', 'collection', array('type' => 'text', 'allow_add' => true, 'allow_delete' => true))
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $choices = array_values($data['choices']);
                $data['choices'] = array_combine($choices, $choices);
                $event->setData($data);
            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFormViewOptions(FormBuilderInterface $builder, FormViewField $formViewField)
    {
        $builder
            ->add('empty_value', 'text', array('required' => false))
            ->add('expanded', 'checkbox', array('required' => false))
            ->add('multiple', 'checkbox', array('required' => false))
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
    public function buildForm(FieldContext $context, FormBuilderInterface $form, FormViewField $formViewField)
    {
        $choices = $context->getOption('choices');
        foreach ($choices as $key => $choice) {
            $choices[$key] = implode('.', array($context->getField()->getTranslatedName(), $choice));
        }

        $form
            ->add($context->getField()->getCamelCaseName(), 'choice', array(
                'choices'            => $context->getOption('choices'),
                'required'           => $context->getOption('required'),
                'placeholder'        => $context->getOption('required') ? false : $formViewField->getOption('empty_value', '—'),
                'label'              => $formViewField->getLabel(),
                'expanded'           => $formViewField->getOption('expanded'),
                'multiple'           => $formViewField->getOption('multiple'),
                'disabled'           => $formViewField->getDisabled(),
                'translation_domain' => 'pum_schema',
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
