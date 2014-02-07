<?php

namespace Pum\Core\Extension\ProjectAdmin\Form\Type;

use Pum\Core\ObjectFactory;
use Pum\Core\Extension\ProjectAdmin\Form\Listener\PumObjectListener;
use Pum\Bundle\CoreBundle\PumContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

class PumObjectType extends AbstractType
{
    protected $objectFactory;
    protected $context;

    public function __construct(ObjectFactory $objectFactory, PumContext $context)
    {
        $this->objectFactory = $objectFactory;
        $this->context = $context;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new PumObjectListener($this->objectFactory));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'with_submit' => true,
            'form_view'   => null,
            'pum_object' => null,
            'data_class' => function (Options $options, $v){
                if ($v) {
                    return $v;
                }

                return $this->objectFactory->getClassName($this->context->getProjectName(), $options['pum_object']);
            }
        ));
    }

    public function getName()
    {
        return 'pum_object';
    }
}
