<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SeoSchemaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objects', 'ww_seo_collection', array(
                'label' => ' ',
                'options' => array(
                    'formType'    => $options['formType']
                )
            ))
            ->add('save', 'submit')
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $seoSchema = $event->getData();
            $seoSchema->saveSeoSchema();
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Seo\SeoSchema',
            'formType'   => 'order',
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'ww_seo_schema';
    }
}
