<?php

namespace Pum\Bundle\ProjectAdminBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class CustomViewRepository extends EntityRepository
{
    const CUSTOMVIEW_CLASS = 'Pum\Bundle\ProjectAdminBundle\Entity\CustomView';

    public function getPage($page = 1)
    {
        $page = max(1, (int) $page);

        $pager = new Pagerfanta(new DoctrineORMAdapter($this->createQueryBuilder('u')->orderBy('u.id', 'ASC')));
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

    public function existedCustomView($user, $project, $beam, $object)
    {
        $customView = $this
            ->createQueryBuilder('g')
            ->select('COUNT(g.id)')

            ->setParameters(array(
                'user'    => $user,
                'project' => $project,
                'beam'    => $beam,
                'object'  => $object,
            ))
        ;

        if (null !== $accessType) {
            $users->andWhere('g.accesstype = :accessType');
        }

        if (null !== $startDate && null !== $startDate) {
            $users->andWhere('g.createDate >= :startDate');
            $users->andWhere('g.createDate <= :endDate');
        } elseif (null !== $startDate) {
            $users->andWhere('g.createDate = :startDate');
        } elseif (null !== $endDate) {
            $users->andWhere('g.createDate = :endDate');
        }

        return $users
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
