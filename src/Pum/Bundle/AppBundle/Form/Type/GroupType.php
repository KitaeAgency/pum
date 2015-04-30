<?php

namespace Pum\Bundle\AppBundle\Form\Type;

use Pum\Bundle\AppBundle\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user  = $options['user'];
        $group = $builder->getData();

        if (null !== $group) {
            $canEdit = $user->canEditPermissions($builder->getData());
            $permissions = Group::getKnownPermissions();

            $builder
                ->add('alias', 'text', array('data' => $group->getAliasName()))
                ->add('name', 'text', array('data'  => $group->getName(), 'disabled' => true))
            ;
        } else {
            $canEdit     = true;
            $permissions = $user->getRoles();

            $builder
                ->add('alias', 'text', array(
                    'attr' => array(
                        'data-text-prefix' => 'group_',
                        'data-copy-input'  => '#pum_group_name',
                        'data-text-camelize' => true,
                        'class' => 'copy-input'
                    )
                ))
                ->add('name', 'text', array(
                    'read_only' => true,
                ))
            ;
        }

        $builder
            ->add('permissions', 'choice', array(
                'choices'            => array_combine($permissions, $permissions),
                'multiple'           => true,
                'expanded'           => true,
                'disabled'           => !$canEdit,
                'translation_domain' => 'pum_form'
            ))
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Pum\Bundle\AppBundle\Entity\Group',
            'translation_domain' => 'pum_form',
            'user'               => null
        ));
    }

    public function getName()
    {
        return 'pum_group';
    }
}
