<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Bundle\WoodworkBundle\Form\Listener\TypeOptionsListener;
use Pum\Bundle\WoodworkBundle\Form\Listener\FieldListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventSubscriber(new FieldListener())
            ->addEventSubscriber(new TypeOptionsListener())
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Definition\FieldDefinition'
        ));
    }

    public function getName()
    {
        return 'ww_field_definition';
    }
}
