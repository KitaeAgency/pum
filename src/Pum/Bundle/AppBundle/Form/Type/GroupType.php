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
        $permissions = Group::getKnownPermissions();
        $groupDefinition = $builder->getData();

        if (null !== $groupDefinition) {
            $builder
                ->add('alias', 'text', array('data' => $groupDefinition->getAliasName()))
                ->add('name', 'text', array('data' => $groupDefinition->getName(), 'disabled' => true));
        } else {
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
                    'data' => 'group_',
                    'read_only' => true,
                ));
        }

        $builder->add('admin', 'checkbox', array('required' => false))
            ->add('permissions', 'choice', array(
                'choices'  => array_combine($permissions, $permissions),
                'multiple' => true,
                'expanded' => true
            ))
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'        => 'Pum\Bundle\AppBundle\Entity\Group',
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'pum_group';
    }
}
