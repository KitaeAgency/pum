<?php

namespace Pum\Core\Object;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ObjectRepository extends EntityRepository
{
    public function addOrderCriteria(QueryBuilder $qb, $sort, $order)
    {
        $class          = $this->getClassname();
        $objectMetadata = $class::_pumGetMetadata();
        $qb             = $objectMetadata->getType($sort)->addOrderCriteria($qb, $sort, $objectMetadata->getTypeOptions($sort), $order);

        return $qb;
    }

    public function addFilterCriteria(QueryBuilder $qb, $type, $values)
    {
        $class          = $this->getClassname();
        $objectMetadata = $class::_pumGetMetadata();
        $qb             = $objectMetadata->getType($type)->addFilterCriteria($qb, $type, $values);

        return $qb;
    }

    public function getPage($page = 1, $per_page = 10, $sort = '', $order = '', $filters = array())
    {
        $page = max(1, (int) $page);

        $qb = $this->createQueryBuilder('u');

        if ($sort) {
            if (!in_array($order = strtoupper($order), $orderTypes = array('ASC', 'DESC'))) {
                throw new \RuntimeException(sprintf('Unvalid order value "%s". Available: "%s".', $order, implode(', ', $orderTypes)));
            }

            if ($sort != 'id') {
                $qb = $this->addOrderCriteria($qb, $sort, $order);
            } else {
                $qb->orderby($qb->getRootAlias() . '.id', $order);
            }
        }

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
