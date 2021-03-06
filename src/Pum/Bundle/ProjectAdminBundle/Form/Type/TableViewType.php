<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\View\TableViewField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class TableViewType extends AbstractType
{
    private $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tableView = $builder->getData();

        switch ($options['form_type']) {
            case 'name':
                $builder
                    ->add(
                        $builder->create('tableview', 'section')
                        ->add('name', 'text')
                        ->add('template', 'text', array(
                            'required'  =>  false
                        ))
                        ->add('private', 'checkbox', array(
                            'required'  =>  false
                        ))
                        ->add('create_default', 'checkbox', array(
                            'required'  =>  false,
                            'data'      =>  true,
                            'mapped'    =>  false
                        ))
                    )
                ;

                $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                    $data = $event->getData();

                    if (isset($data['tableview']['create_default']) && $data['tableview']['create_default']) {
                        $tableView = $event->getForm()->getData();

                        $i = 1;
                        foreach ($tableView->getObjectDefinition()->getFields() as $field) {
                            $tableView->createColumn($field->getName(), $field, TableViewField::DEFAULT_VIEW, $i++);
                        }
                    }
                });
                break;

            case 'columns':
                $tableviewSection = $builder->create('tableview', 'section');
                $tableviewSection
                    ->add('name', 'text')
                    ->add('private', 'checkbox', array(
                        'required'  => false
                    ))
                ;

                if (true === $this->securityContext->isGranted('ROLE_PA_DEFAULT_VIEWS')) {
                    $tableviewSection
                        ->add('default', 'checkbox', array(
                            'required'  => false,
                        ))
                    ;
                }

                $builder
                    ->add($tableviewSection)
                    ->add(
                        $builder->create('preferred_view', 'section')
                        ->add('preferred_object_view', 'entity', array(
                            'class'       => 'Pum\Core\Definition\View\ObjectView',
                            'choice_list' => new ObjectChoiceList($tableView->getObjectDefinition()->getObjectViews(), 'name', array(), null, 'name'),
                            'required'    => false,
                            'empty_value' => 'default'
                        ))
                        ->add('preferred_form_view', 'entity', array(
                            'class'       => 'Pum\Core\Definition\View\FormView',
                            'choice_list' => new ObjectChoiceList($tableView->getObjectDefinition()->getFormEditViews(), 'name', array(), null, 'name'),
                            'required'    => false,
                            'empty_value' => 'default'
                        ))
                        ->add('preferred_form_create_view', 'entity', array(
                            'class'       => 'Pum\Core\Definition\View\FormView',
                            'choice_list' => new ObjectChoiceList($tableView->getObjectDefinition()->getFormCreateViews(), 'name', array(), null, 'name'),
                            'required'    => false,
                            'empty_value' => 'default'
                         ))
                    )
                    ->add('columns', 'pa_tableview_column_collection', array(
                        'options'      => array(
                            'required'      =>  false,
                            'table_view'    =>  $tableView
                        )
                    ))
                ;
                break;

            case 'sort':
                $builder
                    ->add('default_sort', 'pa_tableview_sort', array(
                        'table_view' => $tableView
                    ))
                ;
                break;

            case 'filters':
                $builder
                    ->add('columns', 'pa_tableview_filter_column_collection')
                ;
                break;
        }

        if ($options['with_submit']) {
            $builder->add('save', 'submit');
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  => 'Pum\Core\Definition\View\TableView',
            'form_type'   => 'name',
            'with_submit' => true,
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'pa_tableview';
    }
}
