<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;

class PermissionRepository extends EntityRepository
{
    const PERMISSION_CLASS = 'Pum\Bundle\AppBundle\Entity\Permission';

    public function getGroupPermissions($group, $withInstance = true)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->andWhere($qb->expr()->eq('p.group', ':group'))
            ->setParameters(array(
                'group' => $group,
            ))
        ;

        if (false === $withInstance) {
            $qb->andWhere($qb->expr()->isNull('p.instance'));
        }

        return $qb->getQuery()->getResult();
    }

    public function getPage($page = 1)
    {
        $page = max(1, (int) $page);

        $pager = new Pagerfanta(new DoctrineORMAdapter($this->createQueryBuilder('u')->orderBy('u.id', 'ASC')));
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function save(Permission $permission)
    {
        $em = $this->getEntityManager();
        $em->persist($permission);
        $em->flush();
    }

    public function delete(Permission $permission)
    {
        $em = $this->getEntityManager();
        $em->remove($permission);
        $em->flush();
    }
}
