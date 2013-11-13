<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\Definition\ObjectDefinition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

class ObjectDefinitionSeoType extends AbstractType
{
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
                'label' => 'Activate Routing'
            ))
            ->add('seoField', 'entity', array(
                    'label' => 'Routing field',
                    'class'    => 'Pum\Core\Definition\FieldDefinition',
                    'choice_list' => new ObjectChoiceList($fields, 'name', array(), 'object.name', 'name')
                ))
            /*->add('seoField', 'entity', array('class' => 'Pum\Core\Definition\FieldDefinition', 'property' => 'name', 'group_by' => 'object.name'))*/
        ;

        if (null !== $options['bundlesName'] && null !== $options['rootDir']) {
            $templates = SeoType::getTemplatesFolders($options['rootDir'], $options['bundlesName']);
            $builder->add('seoTemplate', 'choice', array(
                'label' => 'Default template',
                'choices'     => array_combine($templates, $templates),
                'empty_value' => 'Choose a template',
            ));
        } else {
            $builder->add('seoTemplate', 'text', array(
                'label' => 'Default template'
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'inherit_data' => true,
            'rootDir'      => null,
            'bundlesName'  => null
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
