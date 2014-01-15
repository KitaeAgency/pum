<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TableViewFilterCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();

            if ($data instanceof TableViewField) {
                throw new \RuntimeException(sprintf('No TableViewField view set in form.'));
            }

            $event->getForm()->add('filters', 'collection', array(
                'label'        => $data->getLabel(),
                'type'         => 'pa_tableview_filter',
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'options'      => array(
                    'pum_type' => $data->getField()->getType()
                ),
            ));
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Definition\View\TableViewField',
            'translation_domain' => 'pum_form'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pa_tableview_filter_collection';
    }
}
