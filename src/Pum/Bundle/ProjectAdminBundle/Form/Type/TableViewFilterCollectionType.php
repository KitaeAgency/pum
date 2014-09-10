<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pum\Core\Extension\Core\Type\RelationType;

class TableViewFilterCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();

            if ($data instanceof TableViewField) {
                throw new \RuntimeException(sprintf('No TableViewField view set in form.'));
            }

            $hasFilter = true;
            $field     = $data->getField();
            $type      = $field->getType();

            // No filters on one-to-many or many-to-many relation for now
            if ($type == 'relation' && in_array($field->getTypeOption('type'), array(RelationType::ONE_TO_MANY, RelationType::MANY_TO_MANY))) {
                $hasFilter = false;
            }
            

            if ($hasFilter) {
                $event->getForm()->add('filters', 'collection', array(
                    'label'        => $data->getLabel(),
                    'type'         => 'pa_tableview_filter',
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'options'      => array(
                        'pum_type' => $type
                    ),
                ));
            }
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
