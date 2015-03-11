<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ObjectDefinitionType extends AbstractType
{
    /**
     * @var SecurityContextInterface
     */
    protected $context;

    public function __construct(SecurityContextInterface $context)
    {
        $this->context = $context;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $objectDefinition = $builder->getData();

        if (null !== $objectDefinition) {
            $builder
                ->add('alias', 'text', array('data' => $objectDefinition->getAliasName()))
                ->add('name', 'text', array('data' => $objectDefinition->getName(),'disabled' => true))
                ->add('classname', 'text', array('required' => false))
                ->add('repositoryClass', 'text', array('required' => false, 'attr' => array('placeholder' => 'ww.form.object.definition.repositoryclass.placeholder')))
                ->add('fields', 'ww_field_definition_collection')
            ;
        } else {
            $builder
                ->add('alias', 'text', array(
                    'attr' => array(
                        'data-text-prefix' => $options['beam'].'_',
                        'data-copy-input'  => '#ww_object_definition_name',
                        'data-text-camelize' => true,
                        'class' => 'copy-input'
                    )
                ))
                ->add('name', 'text', array(
                    'data' => $options['beam'].'_',
                    'read_only' => true,
                ))
                ->add('classname', 'text', array('required' => false))
                ->add('repositoryClass', 'text', array('required' => false, 'attr' => array('placeholder' => 'ww.form.object.definition.repositoryclass.placeholder')))
                ->add('fields', 'ww_field_definition_collection')
            ;
        }

        $builder
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Pum\Core\Definition\ObjectDefinition',
            'translation_domain' => 'pum_form',
            'beam'               => null
        ));
    }

    public function getName()
    {
        return 'ww_object_definition';
    }
}
