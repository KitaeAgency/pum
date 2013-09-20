<?php

namespace Pum\Extension\ProjectAdmin;

use Doctrine\ORM\QueryBuilder;
use Pum\Core\Context\FieldContext;
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

    public function buildForm(FieldContext $context, FormBuilderInterface $builder);
    public function buildFilterForm(FormBuilderInterface $builder);
    public function addOrderCriteria(FieldContext $context, QueryBuilder $qb, $order);
    public function addFilterCriteria(FieldContext $context, QueryBuilder $qb, $filter);
}
