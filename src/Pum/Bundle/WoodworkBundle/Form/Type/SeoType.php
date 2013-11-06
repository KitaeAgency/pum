<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\Relation\Relation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SeoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $builder          = $event->getForm();
            $objectDefinition = $event->getData();

            $builder
                ->add('seoOrder', 'number', array(
                    'label' => $objectDefinition->getName(),
                    'attr' => array(
                        //'data-sequence' => 'true'
                    )
                ))
            ;
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  => 'Pum\Core\Definition\ObjectDefinition'
        ));
    }

    public function getName()
    {
        return 'ww_seo';
    }
}