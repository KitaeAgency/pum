<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\Definition\ObjectDefinition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

class ObjectDefinitionTreeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // here, we're not able to use form events because form inherits data.
        // That's a limitation, because of:
        // https://github.com/symfony/symfony/issues/8607

        $objectDefinition = $options['objectDefinition'];

        if ($objectDefinition->isTreeable()) {
            $builder
                ->add('treeEnabled', 'checkbox', array(
                    'required'    => false,
                ))
                ->add('tree', 'ww_object_definition_tree_options', array(
                    'objectDefinition' => $objectDefinition,
                    'required'    => false,
                ))
            ;
        } else {
            $builder
                ->add('treeEnabled', 'checkbox', array(
                    'required' => false,
                    'mapped'   => false
                ))
                ->add($builder->create('tree', 'alert', array(
                    'attr' => array(
                        'class' => 'alert-warning text-center'
                    ),
                    'label' => 'pum.form.alert.object.notreeable'
                )))
                ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'inherit_data' => true
        ));

        $resolver->setRequired(array('objectDefinition'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ww_object_definition_tree';
    }
}
