<?php

namespace Pum\Bundle\AppBundle\Form\Type\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_username', 'text', array('attr' => array('placeholder' => 'pum.login.email')))
            ->add('_password', 'password', array('attr' => array('placeholder' => 'pum.login.pwd')))
            ->add('_remember_me', 'checkbox', array('label' => 'pum.login.rememberme', 'required' => false))
            ->add('submit', 'submit', array('label' => 'pum.login.login'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'pum_form',
        ));
    }

    public function getName()
    {
        return 'app_security_login';
    }
}
