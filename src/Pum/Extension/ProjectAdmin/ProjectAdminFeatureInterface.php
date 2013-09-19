<?php

namespace Pum\Extension\ProjectAdmin;

use Doctrine\ORM\QueryBuilder;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Definition\FieldDefinition;
use Symfony\Component\Form\FormBuilderInterface;

interface ProjectAdminFeatureInterface
{
    /**
     * Adds options to a form builder.
     *
     * Passed FormBuilder is root of options.
     */
    public function buildOptionsForm(FormBuilderInterface $builder);

    public function buildForm(FieldBuildContext $context, FormBuilderInterface $builder);
    public function buildFilterForm(FieldBuildContext $context, FormBuilderInterface $builder);
    public function addOrderCriteria(QueryBuilder $qb, $name, array $options, $order);
    public function addFilterCriteria(QueryBuilder $qb, $name, array $values);
}
