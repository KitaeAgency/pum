<?php

namespace Pum\Core\Extension\ProjectAdmin;

use Doctrine\ORM\QueryBuilder;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\FieldDefinition;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

interface ProjectAdminFeatureInterface
{
    /**
     * Adds options to a form builder.
     *
     * Passed FormBuilder is root of options.
     */
    public function buildOptionsForm(FormBuilderInterface $builder);

    public function buildForm(FieldContext $context, FormInterface $form);
    public function buildFilterForm(FormBuilderInterface $builder);
    public function addOrderCriteria(FieldContext $context, QueryBuilder $qb, $order);
    public function addFilterCriteria(FieldContext $context, QueryBuilder $qb, $filter);
}
