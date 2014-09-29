<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\Definition\ObjectDefinition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\Finder\Finder;
use Pum\Bundle\CoreBundle\Routing\PumTemplateFinder;

class ObjectDefinitionSeoType extends AbstractType
{
    protected $templateFinder;

    public function __construct(PumTemplateFinder $templateFinder)
    {
        $this->templateFinder = $templateFinder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // here, we're not able to use form events because form inherits data.
        // That's a limitation, because of:
        // https://github.com/symfony/symfony/issues/8607

        $fields = array();
        $objectDefinition = $options['objectDefinition'];
        foreach ($objectDefinition->getFields() as $field) {
            if ($field->getType() != 'relation') {
                $fields[] = $field;
            }
        }

        $builder
            ->add('seoEnabled', 'checkbox', array(
                'required'    => false,
            ))
            ->add('seoField', 'entity', array(
                    'class'       => 'Pum\Core\Definition\FieldDefinition',
                    'choice_list' => new ObjectChoiceList($fields, 'name', array(), 'object.name', 'name'),
                    'required'    => false,
                ))
            /*->add('seoField', 'entity', array('class' => 'Pum\Core\Definition\FieldDefinition', 'property' => 'name', 'group_by' => 'object.name'))*/
        ;

        $templates = $this->templateFinder->getRoutingTemplates();

        $builder->add('seoTemplate', 'choice', array(
            'choices'     => array_combine($templates, $templates),
            'empty_value' => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'inherit_data' => true,
            'translation_domain' => 'pum_form'
        ));

        $resolver->setRequired(array('objectDefinition'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ww_object_definition_seo';
    }

}
