<?php

namespace Pum\Bundle\CoreBundle\Form\Type;

use Pum\Core\ObjectFactory;
use Pum\Core\Extension\ProjectAdmin\Form\Listener\PumObjectListener;
use Pum\Bundle\CoreBundle\PumContext;
use Pum\Core\Extension\Routing\Behavior\SeoBehavior;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Pum\Bundle\CoreBundle\Routing\PumTemplateFinder;

class PumObjectRoutingType extends AbstractType
{
    protected $templateFinder;

    public function __construct(PumTemplateFinder $templateFinder)
    {
        $this->templateFinder = $templateFinder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $obj       = $options['routing_object'];
        $templates = $this->templateFinder->getRoutingTemplates();

        $builder
            ->add(SeoBehavior::getCamelCaseSlugField(), 'text', array(
                'required' => false,
                'attr' => array(
                    'placeholder' => $obj->getSeoKey()
                )
            ))
            ->add(SeoBehavior::getCamelCaseTemplateField(), 'choice', array(
                'choices'  => array_combine($templates, $templates),
                'required' => false
            ));
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'routing_object'     => null,
            'translation_domain' => 'pum_form',
            'pum_object' => function (Options $options) {
                return $options['routing_object']::PUM_OBJECT;
            }
        ));

        $resolver->setRequired(array('routing_object'));
    }

    public function getParent(){
        return 'pum_object';
    }

    public function getName()
    {
        return 'pum_object_routing';
    }
}
