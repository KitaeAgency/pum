<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\View\FormViewField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

/**
 * Edition of a form view field
 */
class FormViewFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formView = $options['form_view'];

        $builder
            ->add('label', 'text')
            ->add('field', 'choice', array(
                'choice_list' => new ObjectChoiceList($formView->getObjectDefinition()->getFields(), 'name', array(), null, 'name')
            ))
            ->add('placeholder', 'text')
            ->add('help', 'textarea')
            ->add('sequence', 'number')
            ->add('view', 'text', array('disabled' => true, 'data' => FormViewField::DEFAULT_VIEW))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Definition\View\FormViewField',
            'form_view'  => null
        ));

        $resolver->setRequired(array('form_view'));
    }

    public function getName()
    {
        return 'pa_formview_field';
    }
}
