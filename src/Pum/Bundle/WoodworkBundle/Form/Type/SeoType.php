<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\Relation\Relation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Pum\Bundle\CoreBundle\Routing\PumTemplateFinder;

class SeoType extends AbstractType
{
    protected $templateFinder;

    public function __construct(PumTemplateFinder $templateFinder)
    {
        $this->templateFinder = $templateFinder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
            $builder          = $event->getForm();
            $objectDefinition = $event->getData();

            if ($options['formType'] !== 'template') {
                $builder
                    ->add('seoOrder', 'number', array(
                        'label' => $objectDefinition->getAliasName(),
                        'attr' => array(
                            'data-sequence' => 'single'
                        )
                    ))
                ;
            } else {
                $templates = $this->templateFinder->getRoutingTemplates();

                $builder->add('seoTemplate', 'choice', array(
                    'label' => $objectDefinition->getAliasName(),
                    'choices'     => array_combine($templates, $templates),
                    'empty_value' => true
                ));
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  => 'Pum\Core\Definition\ObjectDefinition',
            'formType'    => null,
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'ww_seo';
    }
}