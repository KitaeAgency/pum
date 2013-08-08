<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Bundle\WoodworkBundle\Entity\Group;
use Pum\Core\Definition\Relation;
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
        $builder
            ->add('name',        'text')
            ->add('permissions', 'choice', array(
                'choices'  => array_combine(Group::$knownPermissions, Group::$knownPermissions),
                'multiple' => true,
                'expanded' => true
            ))
            ->add('users', 'entity', array(
                'class' => 'Pum\Bundle\WoodworkBundle\Entity\User',
                'property' => 'fullname',
                'expanded' => true,
                'multiple' => true
            ))
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'        => 'Pum\Bundle\WoodworkBundle\Entity\Group',
        ));
    }

    public function getName()
    {
        return 'ww_group';
    }
}
