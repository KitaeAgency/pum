<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\View\TableViewSort;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

class TableViewSortType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $tvs = $event->getData();

            if (!$tvs instanceof TableViewSort) {
                throw new \Exception('Unable to build form from data');
            }

            $form
                ->add('column', 'entity', array(
                    'class'       => 'Pum\Core\Definition\View\TableViewField',
                    'choice_list' => new ObjectChoiceList($tvs->getTableView()->getColumns(), 'label', array(), null, 'id'),
                    'required'    => false,
                    'empty_value' => 'id'
                ))
                ->add('order', 'choice', array('choices' => array_combine(TableViewSort::getOrderTypes(), TableViewSort::getOrderTypes())))
            ;
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Definition\View\TableViewSort',
            'table_view' => null
        ));

        $resolver->setRequired(array('table_view'));
    }

    public function getName()
    {
        return 'pa_tableview_sort';
    }
}
