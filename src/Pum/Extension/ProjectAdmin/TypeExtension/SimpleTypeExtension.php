<?php

namespace Pum\Extension\ProjectAdmin\TypeExtension;

use Doctrine\ORM\QueryBuilder;
use Pum\Core\AbstractTypeExtension;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\FieldDefinition;
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

    public function buildForm(FieldContext $context, FormBuilderInterface $builder)
    {
        $builder->add($context->getFieldName(), $context->getOption('form_type'), $context->getOption('form_options'));
    }

    public function buildFilterForm(FieldContext $context, FormBuilderInterface $builder)
    {
        die('@todo Simple::buildFilter');
    }
    public function addOrderCriteria(FieldContext $context, QueryBuilder $qb, $order)
    {
        die('@todo Simple::addOrderCriteria');
    }

    public function addFilterCriteria(FieldContext $context, QueryBuilder $qb, $filter)
    {
        die('@todo Simple::addFilterCriteria');
    }
}
