<?php

namespace Pum\Bundle\ProjectAdminBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class CustomViewRepository extends EntityRepository
{
    const CUSTOMVIEW_CLASS = 'Pum\Bundle\ProjectAdminBundle\Entity\CustomView';

    public function getPage($page = 1, $project = null, $user = null, $group = null, $object = null)
    {
        $page = max(1, (int) $page);
        $qb   = $this
            ->createQueryBuilder('u')->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
        ;

        if (null !== $project) {
            $qb
                ->andWhere('u.project = :project')
                ->setParameter('project', $project)
            ;
        }

        if (null !== $user) {
            $qb
                ->andWhere('u.user = :user')
                ->setParameter('user', $user)
            ;
        }

        if (null !== $group) {
            $qb
                ->andWhere('u.group = :group')
                ->setParameter('group', $group)
            ;
        }

        if (null !== $object) {
            $qb
                ->andWhere('u.object = :object')
                ->setParameter('object', $object)
            ;
        }

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb ));
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function save(CustomView $customView)
    {
        $em = $this->getEntityManager();
        $em->persist($customView);
        $em->flush();
    }

    public function delete(CustomView $customView)
    {
        $em = $this->getEntityManager();
        $em->remove($customView);
        $em->flush();
    }

    public function existedCustomViewForUser($user, $project, $beam, $object)
    {
        $customView = $this
            ->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.user = :user')
            ->andWhere('u.project = :project')
            ->andWhere('u.beam = :beam')
            ->andWhere('u.object = :object')
            ->setParameters(array(
                'user'    => $user,
                'project' => $project,
                'beam'    => $beam,
                'object'  => $object,
            ))
        ;

        return  $customView
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getCustomViewForUser($user, $project, $beam, $object)
    {
        $customView = $this
            ->createQueryBuilder('u')
            ->andWhere('u.user = :user')
            ->andWhere('u.project = :project')
            ->andWhere('u.beam = :beam')
            ->andWhere('u.object = :object')
            ->setParameters(array(
                'user'    => $user,
                'project' => $project,
                'beam'    => $beam,
                'object'  => $object,
            ))
        ;

        return  $customView
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
