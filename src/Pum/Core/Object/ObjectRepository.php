<?php

namespace Pum\Core\Object;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ObjectRepository extends EntityRepository
{
    public function getPage($page = 1, $per_page = 10, $filter = '')
    {
        $page = max(1, (int) $page);

        $qb = $this->createQueryBuilder('u');

        $adapter = new DoctrineORMAdapter($qb);

        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($per_page);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
