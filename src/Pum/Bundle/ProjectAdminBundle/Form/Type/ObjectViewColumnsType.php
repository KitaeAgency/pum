<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\ObjectView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectViewColumnsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $objectView = $builder->getData();

        if (!$objectView instanceof ObjectView) {
            throw new \RuntimeException(sprintf('No object view set in form.'));
        }

        $definition  = $objectView->getObjectDefinition();
        $columnNames = $objectView->getColumnNames();

        foreach ($definition->getFields() as $field) {
            foreach ($columnNames as $columnName) {
                if ($objectView->hasColumn($columnName) && $objectView->getColumnField($columnName) == $field->getName()) {
                    continue 2;
                }
            }

            $columnNames[] = $field->getName();
        }

        foreach ($columnNames as $i => $columnName) {
            $builder->add($builder->create($i, 'form', array('mapped' => false))
                ->add('order', 'number', array('data' => $i + 1))
                ->add('label', 'text', array('data' => $columnName))
                ->add('show', 'checkbox', array('data' => $objectView->hasColumn($columnName)))
                ->add('field', 'text', array('data' => $objectView->hasColumn($columnName) ? $objectView->getColumnField($columnName) : $columnName, 'disabled' => true))
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

            $objectView = $event->getData();

            $objectView->removeColumns();

            foreach ($columns as $name => $column) {
                if ($column['show']) {
                    $objectView->addColumn($name, $column['field'], $column['view']);
                }
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\ObjectView',
            'mapped' => false
        ));
    }

    public function getName()
    {
        return 'pa_objectview_columns';
    }
}
