<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\View\ObjectViewField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectViewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $objectView = $builder->getData();

        // add default fields
        if (null === $objectView->getName()) {
            $i = 1;
            foreach ($objectView->getObjectDefinition()->getFields() as $field) {
                $objectView->createField($field->getName(), $field, ObjectViewField::DEFAULT_VIEW, $i++);
            }
        }

        $builder
            ->add($builder->create('objectview', 'section')
                ->add('name', 'text')
                ->add('private', 'checkbox', array(
                    'required'  =>  false
                ))
            )
            ->add('fields', 'pa_objectview_field_collection', array(
                'options' => array(
                    'required'      =>  false,
                    'object_view'   =>  $objectView
                )
            ))
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'            =>  'Pum\Core\Definition\View\ObjectView',
            'translation_domain'    =>  'pum_form'
        ));
    }

    public function getName()
    {
        return 'pa_objectview';
    }
}
