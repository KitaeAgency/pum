<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineMetadata;
use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidationMetadata;

class HtmlType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'is_inline'     => false, // block (<p>, <div>....) --- inline (<br />)
        ));
    }

    public function buildField(FieldBuildContext $context)
    {
        $cb    = $context->getClassBuilder();
        $camel = $context->getField()->getCamelCaseName();

        $cb->createProperty($camel);
        $cb->addGetMethod($camel);
        $cb->addSetMethod($camel);
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'name'      => $context->getField()->getLowercaseName(),
            'fieldName' => $context->getField()->getCamelCaseName(),
            'type'      => 'text',
            'nullable'  => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'html';
    }

    public function buildForm(FieldContext $context, FormInterface $form)
    {
        $name = $context->getField()->getCamelCaseName();

        if ($context->getOption('is_inline')) {
            $toolbar = array(array('Bold', 'Italic', 'Link'));
        } else {
            $toolbar = array(array('Styles', 'Table'));
        }

        $ckeditorConfig = array(
            'toolbar' => $toolbar,
            'customConfig' => '', # disable dynamic config.js loading
        );

        $options = array(
            'attr' => array('data-ckeditor'=> json_encode($ckeditorConfig))
        );

        $form->add($name, 'textarea', $options);
    }

    public function mapValidation(FieldContext $context, ValidationMetadata $metadata)
    {
        // :-)
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('is_inline', 'checkbox', array('required' => false))
        ;
    }
}