<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\Definition\Relation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text')
            ->add('fullname', 'text')
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Bundle\WoodworkBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'ww_user';
    }
}
