<?php

namespace Pum\Bundle\CoreBundle\Form\Type;

use Pum\Core\ObjectFactory;
use Pum\Core\Extension\ProjectAdmin\Form\Listener\PumObjectListener;
use Pum\Bundle\CoreBundle\PumContext;
use Pum\Core\Extension\Security\Behavior\SecurityUserBehavior;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Pum\Bundle\CoreBundle\Routing\PumTemplateFinder;

class PumObjectSecurityType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $obj       = $options['object'];

         $builder
            ->add(SecurityUserBehavior::getCamelCaseRoleField(), 'collection', array(
                'type' => 'text',
                'allow_add' => true,
                'allow_delete' => true,
                'options' => array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'ROLE_USER'
                    )
                )
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'object'     => null,
            'translation_domain' => 'pum_form',
            'pum_object' => function (Options $options) {
                return $options['object']::PUM_OBJECT;
            }
        ));

        $resolver->setRequired(array('object'));
    }

    public function getParent()
    {
        return 'pum_object';
    }

    public function getName()
    {
        return 'pum_object_security';
    }
}
