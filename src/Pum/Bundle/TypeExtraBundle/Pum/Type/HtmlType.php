<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineMetadata;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Definition\FieldDefinition;
use Pum\Extension\EmFactory\EmFactoryFeatureInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class HtmlType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'doctrine_type' => 'text',
            'is_inline'     => false, // block (<p>, <div>....) --- inline (<br />)
            'pa_form_type'  => 'textarea',
            'pa_form_options' => function (Options $options) {
                if ($options['is_inline']) {
                    $toolbar = array(
                        array('Bold', 'Italic', 'Link')
                    );
                } else {
                    $toolbar = array(
                        array('Styles', 'Table')
                    );
                }

                $ckeditorConfig = array(
                    'toolbar' => $toolbar,
                    'customConfig' => '', # disable dynamic config.js loading
                );

                return array(
                    'attr' => array('data-ckeditor'=> json_encode($ckeditorConfig))
                );
            }
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
    public function getName()
    {
        return 'html';
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
