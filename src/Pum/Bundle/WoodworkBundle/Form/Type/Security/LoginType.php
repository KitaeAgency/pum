<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_username', 'text')
            ->add('_password', 'password')
            ->add('_remember_me', 'checkbox', array('required' => false))
            ->add('submit', 'submit', array('label' => 'Signin'))
        ;
    }

    public function getName()
    {
        return 'ww_security_login';
    }
}
