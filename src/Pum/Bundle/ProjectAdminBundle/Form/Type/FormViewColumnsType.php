<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormViewColumnsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formView = $builder->getData();

        if (!$formView instanceof FormView) {
            throw new \RuntimeException(sprintf('No form view set in form.'));
        }

        $definition  = $formView->getObjectDefinition();
        $columnNames = $formView->getColumnNames();

        foreach ($definition->getFields() as $field) {
            foreach ($columnNames as $columnName) {
                if ($formView->hasColumn($columnName) && $formView->getColumnField($columnName) == $field->getName()) {
                    continue 2;
                }
            }

            $columnNames[] = $field->getName();
        }

        foreach ($columnNames as $i => $columnName) {
            $builder->add($builder->create($i, 'form', array('mapped' => false))
                ->add('order', 'number', array('data' => $i + 1))
                ->add('label', 'text', array('data' => $columnName))
                ->add('show', 'checkbox', array('data' => $formView->hasColumn($columnName)))
                ->add('field', 'text', array('data' => $formView->hasColumn($columnName) ? $formView->getColumnField($columnName) : $columnName, 'disabled' => true))
                ->add('view', 'text', array('data' => 'default', 'disabled' => true))
            );
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            $columns = array();
            foreach ($form as $subForm) {
                $columns[$subForm->get('label')->getData()] = array(
                    'order' => $subForm->get('order')->getData(),
                    'show' => $subForm->get('show')->getData(),
                    'field' => $subForm->get('field')->getData(),
                    'view' => $subForm->get('view')->getData(),
                );
            }

            uasort($columns, function ($left, $right) {
                return $left['order'] > $right['order'];
            });

            $formView = $event->getData();

            $formView->removeColumns();

            foreach ($columns as $name => $column) {
                if ($column['show']) {
                    $formView->addColumn($name, $column['field'], $column['view']);
                }
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\FormView',
            'mapped' => false
        ));
    }

    public function getName()
    {
        return 'pa_formview_columns';
    }
}
