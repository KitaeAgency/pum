<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormViewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($builder->create('formview', 'section')
                ->add('name', 'text')
                ->add('private', 'checkbox')
            )
            ->add($builder->create('rows', 'section')
                ->add('rows', 'pa_formview_rows', array('data' => $builder->getData()))
            )
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\View\FormView'
        ));
    }

    public function getName()
    {
        return 'pa_formview';
    }
}
