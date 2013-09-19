<?php

namespace Pum\Extension\ProjectAdmin\TypeExtension;

use Doctrine\ORM\QueryBuilder;
use Pum\Core\AbstractTypeExtension;
use Pum\Core\Context\FieldBuildContext;
use Pum\Extension\ProjectAdmin\ProjectAdminFeatureInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SimpleTypeExtension extends AbstractTypeExtension implements ProjectAdminFeatureInterface
{
    public function getExtendedType()
    {
        return 'simple';
    }

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

    public function buildFilterForm(FieldBuildContext $context, FormBuilderInterface $builder)
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
