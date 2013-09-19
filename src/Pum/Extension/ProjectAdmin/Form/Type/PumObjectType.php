<?php

namespace Pum\Core\Extension\Form\Form\Type;

use Pum\Core\Extension\Form\FormExtension;
use Pum\Core\Extension\Form\Form\Listener\PumObjectListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PumObjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new PumObjectListener());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'with_submit' => true,
            'form_view'   => null
        ));
    }

    public function getName()
    {
        return 'pum_object';
    }
}
