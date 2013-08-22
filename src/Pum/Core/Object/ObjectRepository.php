<?php

namespace Pum\Core\Object;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ObjectRepository extends EntityRepository
{
    public function getPage($page = 1)
    {
        $page = max(1, (int) $page);

        $pager = new Pagerfanta(new DoctrineORMAdapter($this->createQueryBuilder('u')));
        $pager->setCurrentPage($page);

        return $pager;
    }
}
