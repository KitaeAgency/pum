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

            switch ($options['type']) {
                case 'behavior':
                    if ($this->context->isGranted('ROLE_PA_ROUTING')) {
                        $builder->add($builder->create('routing', 'section')
                            ->add('seo', 'ww_object_definition_seo', array(
                                'label' => ' ',
                                'attr' => array(
                                    'class' => 'pum-scheme-panel-amethyst'
                                ),
                                'objectDefinition' => $objectDefinition
                            ))
                        );
                    }

                    $builder->add($builder->create('security_user', 'section')
                        ->add('user_security', 'ww_object_definition_security_user', array(
                            'label' => ' ',
                            'attr' => array(
                                'class' => 'pum-scheme-panel-carrot'
                            ),
                            'objectDefinition' => $objectDefinition
                        ))
                    );

                    $builder->add($builder->create('searchable', 'section')
                        ->add('searchable', 'ww_object_definition_searchable', array(
                            'label' => ' ',
                            'attr' => array(
                                'class' => 'pum-scheme-panel-sanguine'
                            ),
                            'objectDefinition' => $objectDefinition
                        ))
                    );
                    break;

                default:
                    $builder
                        ->add('alias', 'text', array('data' => $objectDefinition->getAliasName()))
                        ->add('name', 'text', array('data' => $objectDefinition->getName(),'disabled' => true))
                        ->add('classname', 'text', array('required' => false))
                        ->add('repositoryClass', 'text', array('required' => false, 'attr' => array('placeholder' => 'ww.form.object.definition.repositoryclass.placeholder')))
                        ->add('fields', 'ww_field_definition_collection')
                    ;
                    break;
            }

        } else {
            $builder
                ->add('alias', 'text', array(
                    'attr' => array(
                        'data-text-prefix' => $options['beam'].'_',
                        'data-copy-input'  => '#ww_object_definition_name',
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
            'type'               => 'overall',
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
