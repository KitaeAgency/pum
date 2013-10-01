<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\View\ObjectViewField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

/**
 * Edition of a object view field
 */
class ObjectViewFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $objectView = $options['object_view'];

        $builder
            ->add('label', 'text')
            ->add('field', 'choice', array(
                'choice_list' => new ObjectChoiceList($objectView->getObjectDefinition()->getFields(), 'name', array(), null, 'name')
            ))
            ->add('sequence', 'number')
            ->add('view', 'text', array('disabled' => true, 'data' => ObjectViewField::DEFAULT_VIEW))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Definition\View\ObjectViewField',
            'object_view'  => null
        ));

        $resolver->setRequired(array('object_view'));
    }

    public function getName()
    {
        return 'pa_objectview_field';
    }
}
