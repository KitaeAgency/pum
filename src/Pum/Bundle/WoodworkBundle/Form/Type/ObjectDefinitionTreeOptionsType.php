<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

class ObjectDefinitionTreeOptionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // here, we're not able to use form events because form inherits data.
        // That's a limitation, because of:
        // https://github.com/symfony/symfony/issues/8607

        $objectDefinition = $options['objectDefinition'];
        $labelFields      = array();
        $treeFields       = $objectDefinition->getTreeableFields();

        foreach ($objectDefinition->getFields() as $field) {
            if ($field->getType() == 'text' || $field->getType() == 'email') {
                $labelFields[] = $field;
            }
        }

        $builder
            ->add('labelField', 'entity', array(
                'class'       => 'Pum\Core\Definition\FieldDefinition',
                'choice_list' => new ObjectChoiceList($labelFields, 'name', array(), 'object.name', 'name'),
                'required'    => true,
            ))
            ->add('treeField', 'entity', array(
                'class'       => 'Pum\Core\Definition\FieldDefinition',
                'choice_list' => new ObjectChoiceList($treeFields, 'name', array(), 'object.name', 'name'),
                'required'    => true,
            ))
            ->add('icon', 'pum_icon')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Definition\Tree'
        ));

        $resolver->setRequired(array('objectDefinition'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ww_object_definition_tree_options';
    }
}
