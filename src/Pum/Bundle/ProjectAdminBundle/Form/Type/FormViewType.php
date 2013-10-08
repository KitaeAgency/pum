<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\View\FormViewField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormViewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formView = $builder->getData();

        // add default fields
        if (null === $formView->getName()) {
            $i = 1;
            foreach ($formView->getObjectDefinition()->getFields() as $field) {
                $formView->createField($field->getName(), $field, FormViewField::DEFAULT_VIEW, $i++);
            }
        }

        $builder
            ->add($builder->create('formview', 'section')
                ->add('name', 'text')
                ->add('private', 'checkbox')
            )
            ->add($builder->create('rows', 'section')
                ->add('fields', 'pa_formview_field_collection', array(
                    'options' => array(
                        'form_view' => $formView
                    )
                ))
            )
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\View\FormView'
        ));
    }

    public function getName()
    {
        return 'pa_formview';
    }
}
