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
            $overallTab = $builder->create('overall', 'pum_tab')
                ->add('name', 'text')
                ->add('classname', 'text', array('required' => false))
                ->add('repositoryClass', 'text', array('required' => false, 'attr' => array('placeholder' => 'ww.form.object.definition.repositoryclass.placeholder')))
                ->add('fields', 'ww_field_definition_collection')
            ;

            $behaviorsTab = $builder->create('behaviors', 'pum_tab');

            if ($this->context->isGranted('ROLE_PA_ROUTING')) {
                $routingSection = $builder->create('routing', 'section')
                    ->add('seo', 'ww_object_definition_seo', array(
                        'label' => ' ',
                        'attr' => array(
                            'class' => 'pum-scheme-panel-amethyst'
                        ),
                        'objectDefinition' => $objectDefinition
                    ))
                ;
                $behaviorsTab->add($routingSection);
            }

            $securitySection = $builder->create('security_user', 'section')
                ->add('user_security', 'ww_object_definition_security_user', array(
                    'label' => ' ',
                    'attr' => array(
                        'class' => 'pum-scheme-panel-carrot'
                    ),
                    'objectDefinition' => $objectDefinition
                ))
            ;
            $behaviorsTab->add($securitySection);

            $searchableTab = $builder->create('searchable', 'section')
                ->add('searchable', 'ww_object_definition_searchable', array(
                    'label' => ' ',
                    'attr' => array(
                        'class' => 'pum-scheme-panel-sanguine'
                    ),
                    'objectDefinition' => $objectDefinition
                ))
            ;
            $behaviorsTab->add($searchableTab);

            $tabs = $builder->create('tabs', 'pum_tabs')
                ->add($overallTab)
                ->add($behaviorsTab)
            ;

            $builder->add($tabs);

        } else {
            $builder
                ->add('name', 'text')
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
            'data_class'  => 'Pum\Core\Definition\ObjectDefinition',
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'ww_object_definition';
    }
}
