<?php

namespace Pum\Extension\ProjectAdmin\Form\Type;

use Pum\Core\ObjectFactory;
use Pum\Extension\Form\FormExtension;
use Pum\Extension\ProjectAdmin\Form\Listener\PumObjectListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PumObjectType extends AbstractType
{
    protected $objectFactory;

    public function __construct(ObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new PumObjectListener($this->objectFactory));
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
