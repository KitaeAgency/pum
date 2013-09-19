<?php

namespace Pum\Extension\ProjectAdmin\TypeExtension;

use Pum\Extension\ProjectAdmin\ProjectAdminFeatureInterface;

class SimpleTypeExtension implements ProjectAdminFeatureInterface
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'form_type'    => 'text',
            'form_options' => array()
        ));
    }

    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('required', 'checkbox')
        ;
    }

    public function buildForm(FieldBuildContext $context, FormBuilderInterface $builder)
    {
        $builder->add($context->getFieldName(), $context->getOption('form_type'), $context->getOption('form_options'));
    }

    public function buildFilterForm(FormBuilderInterface, FieldDefinition $field)
    {
        die('@todo');
    }
    public function addOrderCriteria(QueryBuilder $qb, $name, array $options, $order)
    {
        die('@todo');
    }

    public function addFilterCriteria(QueryBuilder $qb, $name, array $values)
    {
        die('@todo');
    }
}
