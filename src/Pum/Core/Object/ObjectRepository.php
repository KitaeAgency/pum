<?php

namespace Pum\Core\Object;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\FieldDefinition;

class ObjectRepository extends EntityRepository
{
    public function getTypeHierarchyAndFieldContext($field)
    {
        $class         = $this->getClassname();
        $objectFactory = $this->_em->getObjectFactory();

        $project  = $objectFactory->getProject($class::PUM_PROJECT);
        $context  = new FieldContext($project, $field, $field->getTypeOptions());
        $features = $objectFactory->getTypeHierarchy($sortField->getType(), 'Pum\Core\Extension\ProjectAdmin\ProjectAdminFeatureInterface');

        return array($features, $context);
    }

    public function addOrderCriteria(QueryBuilder $qb, $field, $order)
    {
        list($features, $context) = $this->getTypeHierarchyAndFieldContext($field);

        foreach ($features as $feature) {
            $qb = $feature->addOrderCriteria($context, $qb, $order);
        }

        return $qb;
    }

    public function addFilterCriteria(QueryBuilder $qb, $field, $values)
    {
        list($features, $context) = $this->getTypeHierarchyAndFieldContext($field);

        foreach ($features as $feature) {
            $qb = $feature->addFilterCriteria($context, $qb, $values);
        }

        return $qb;
    }

    public function getPage($page = 1, $per_page = 10, FieldDefinition $sortField = null, $order = 'asc', $filters = array())
    {
        $page = max(1, (int) $page);

        $qb = $this->createQueryBuilder('u');

        // Order stuff
        if (!is_null($sortField)) {
            $qb = $this->addOrderCriteria($qb, $sortField, $order);
        } else {
            $qb->orderby($qb->getRootAlias() . '.id', $order);
        }

        // Filters stuff
        if ($filters) {
            foreach ($filters as $filter) {
                foreach ($filter['filters'] as $filterObj) {
                    $qb = $this->addFilterCriteria($qb, $filter['field'], array(
                        'type'  => $filterObj->getType(), 
                        'value' => $filterObj->getValue()
                    ));
                }
            }
        }

        $adapter = new DoctrineORMAdapter($qb);

        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($per_page);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
