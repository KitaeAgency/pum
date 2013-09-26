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
    public function addOrderCriteria(QueryBuilder $qb, $sortField, $order)
    {
        $class         = $this->getClassname();
        $objectFactory = $this->_em->getObjectFactory();

        $project  = $objectFactory->getProject($class::PUM_PROJECT);
        $context  = new FieldContext($project, $sortField, $sortField->getTypeOptions());
        $features = $objectFactory->getTypeHierarchy($sortField->getType(), 'Pum\Extension\ProjectAdmin\ProjectAdminFeatureInterface');

        foreach ($features as $feature) {
            $qb = $feature->addOrderCriteria($context, $qb, $order);
        }

        return $qb;
    }

    public function addFilterCriteria(QueryBuilder $qb, $type, $values)
    {
        /*$class          = $this->getClassname();
        $objectMetadata = $class::_pumGetMetadata();
        $qb             = $objectMetadata->getType($type)->addFilterCriteria($qb, $type, $values);*/

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
                list($field, $values) = $filter;
                foreach ((array)$values as $key => $value) {
                    $qb = $this->addFilterCriteria($qb, $field, $value);
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
