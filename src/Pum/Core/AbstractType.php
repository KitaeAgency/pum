<?php

namespace Pum\Core;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Extension\EmFactory\EmFactoryFeatureInterface;
use Pum\Extension\ProjectAdmin\ProjectAdminFeatureInterface;
use Pum\Extension\Validation\ValidationFeatureInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

abstract class AbstractType implements TypeInterface, EmFactoryFeatureInterface, ProjectAdminFeatureInterface, ValidationFeatureInterface
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildField(FieldBuildContext $context)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FieldContext $context, FormInterface $form)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilterForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('type', 'choice', array(
                'choices' => array_combine($filterTypes, $filterNames)
            ))
            ->add('value', 'text')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function addOrderCriteria(FieldContext $context, QueryBuilder $qb, $order)
    {
        $by = $qb->getRootAlias() . '.' . $context->getField()->getCamelCaseName();
        $qb->orderby($by, $order);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterCriteria(FieldContext $context, QueryBuilder $qb, $filter)
    {
        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(FieldContext $context, ValidatorClassMetadata $metadata)
    {
    }
}
