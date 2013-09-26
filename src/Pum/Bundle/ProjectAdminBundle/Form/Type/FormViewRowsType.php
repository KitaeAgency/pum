<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\View\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormViewRowsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formView = $builder->getData();

        if (!$formView instanceof FormView) {
            throw new \RuntimeException(sprintf('No form view set in form.'));
        }

        $definition = $formView->getObjectDefinition();
        $rowNames   = array();

        // add to rowNames unshown formView rows
        foreach ($definition->getFields() as $field) {
            foreach ($rowNames as $rowName) {
                if ($formView->hasField($rowName) && $formView->getRowField($rowName) == $field->getName()) {
                    continue 2;
                }
            }
            
            $rowNames[] = $field->getName();
        }

        foreach ($rowNames as $i => $rowName) {
            $builder->add($builder->create($i, 'form', array('mapped' => false))
                ->add('order', 'number', array('data' => $i + 1))
                ->add('label', 'text', array('data' => $rowName))
                ->add('show', 'checkbox', array('data' => $formView->hasField($rowName)))
                ->add('field', 'text', array('data' => $formView->hasField($rowName) ? $formView->getRowField($rowName) : $rowName, 'disabled' => true))
                ->add('view', 'text', array('data' => 'default', 'disabled' => true))
            );
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            $rows = array();
            foreach ($form as $subForm) {
                $rows[$subForm->get('label')->getData()] = array(
                    'order' => $subForm->get('order')->getData(),
                    'show'  => $subForm->get('show')->getData(),
                    'field' => $subForm->get('field')->getData(),
                    'view'  => $subForm->get('view')->getData(),
                );
            }

            uasort($rows, function ($left, $right) {
                return $left['order'] > $right['order'];
            });

            $formView = $event->getData();

            foreach ($rows as $name => $row) {
                if ($row['show']) {
                    $formView->addRow($name, $row['field'], $row['view']);
                }
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\View\FormView',
            'mapped' => false
        ));
    }

    public function getName()
    {
        return 'pa_formview_rows';
    }
}
