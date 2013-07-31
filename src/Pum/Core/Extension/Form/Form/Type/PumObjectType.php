<?php

namespace Pum\Core\Extension\Form\Form\Type;

use Pum\Core\Extension\Form\FormExtension;
use Pum\Core\Extension\Form\Form\Listener\PumObjectListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PumObjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new PumObjectListener());
    }

    public function getName()
    {
        return 'pum_object';
    }
}
