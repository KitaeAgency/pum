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

    public function getPage($page = 1, $per_page = 10, $sort = '', $order = '')
    {
        $page = max(1, (int) $page);

        $qb = $this->createQueryBuilder('u');
        if ($sort) {
            $qb = $this->addOrderCriteria($qb, $sort, $order);
        }

        $adapter = new DoctrineORMAdapter($qb);

        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($per_page);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
