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

    public function buildForm(FieldDefinition $field, FormBuilderInterface $builder);
    public function buildFilterForm(FieldDefinition $field, FormBuilderInterface $builder);
    public function addOrderCriteria(FieldDefinition $field, QueryBuilder $qb, $order);
    public function addFilterCriteria(FieldDefinition $field, QueryBuilder $qb, $filter);
}
