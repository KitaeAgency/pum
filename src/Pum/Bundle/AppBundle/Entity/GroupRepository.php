<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class GroupRepository extends EntityRepository
{
    const GROUP_CLASS = 'Pum\Bundle\AppBundle\Entity\Group';

    public function getAdminGroup()
    {
        return $this->findOneBy(array('admin' => true));
    }

    public function getPage($page = 1)
    {
        $page = max(1, (int) $page);

        $pager = new Pagerfanta(new DoctrineORMAdapter($this->createQueryBuilder('u')->addOrderBy('u.admin', 'DESC')->addOrderBy('u.name', 'ASC')));
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function save(Group $group)
    {
        $em = $this->getEntityManager();
        $em->persist($group);
        $em->flush();
    }

    public function delete(Group $group)
    {
        $em = $this->getEntityManager();
        $em->remove($group);
        $em->flush();
    }
}
