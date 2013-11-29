<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectDefinitionSearchFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $types = array('string', 'integer', 'long', 'float', 'double', 'boolean');
        $builder
            ->add('name', 'text')
            ->add('expression', 'text')
            ->add('weight', 'number')
            ->add('type', 'choice', array(
                'choices' => array_combine($types , $types)
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Definition\SearchField'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ww_object_definition_search_field';
    }
}
