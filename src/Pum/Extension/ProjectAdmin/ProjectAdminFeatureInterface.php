<?php

namespace Pum\Extension\ProjectAdmin;

interface ProjectAdminFeatureInterface
{
    public function buildOptionsForm(FormBuilderInterface $builder, FieldDefinition $field);
    public function buildForm(FormBuilderInterface, FieldDefinition $field);
    public function buildFilterForm(FormBuilderInterface, FieldDefinition $field);
    public function addOrderCriteria(QueryBuilder $qb, $name, array $options, $order);
    public function addFilterCriteria(QueryBuilder $qb, $name, array $values);
}
