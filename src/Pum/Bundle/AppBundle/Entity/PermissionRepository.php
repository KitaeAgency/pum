<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pum\Bundle\AppBundle\Entity\Permission;

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

    public function flush()
    {
        $em = $this->getEntityManager();
        $em->flush();
    }

    public function addPermission($attribute, $group, $project, $beam = null, $object = null, $instance = null)
    {
        // Faster but only work for Doctrine 2.5 Beta for now
        /*$qb = $this->createQueryBuilder('p');
        $qb
            ->insert()
            ->values(array(
                'attribute' => ':attribute',
                'group'     => ':group',
                'project'   => ':project',
                'beam'      => ':beam',
                'object'    => ':object',
                'instance'  => ':instance'
            ))
            ->setParameters(array(
                'attribute' => $attribute,
                'group'     => $group,
                'project'   => $project,
                'beam'      => $beam,
                'object'    => $object,
                'instance'  => $instance
            ))
            ->getQuery()
            ->execute()
        ;*/

        $em = $this->getEntityManager();

        $permission = new Permission();
        $permission
            ->setAttribute($attribute)
            ->setGroup($em->getReference('Pum\Bundle\AppBundle\Entity\Group', $group))
            ->setProject($em->getReference('Pum\Core\Definition\Project', $project))
            ->setBeam((null === $beam) ? null : $em->getReference('Pum\Core\Definition\Beam', $beam))
            ->setObject((null === $object) ? null : $em->getReference('Pum\Core\Definition\ObjectDefinition', $object))
            ->setInstance($instance)
        ;

        $em->persist($permission);
    }

    public function deleteByIds($ids)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->delete()
            ->andWhere($qb->expr()->in('p.id', ':ids'))
            ->setParameters(array(
                'ids' => $ids,
            ))
            ->getQuery()
            ->execute()
        ;
    }
}
