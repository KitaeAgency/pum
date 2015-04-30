<?php

namespace Pum\Bundle\ProjectAdminBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pum\Bundle\AppBundle\Entity\User;
use Pum\Core\Definition\Project;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Doctrine\Common\Collections\ArrayCollection;

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

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
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
            ->andWhere('u.project = :project')
            ->andWhere('u.beam = :beam')
            ->andWhere('u.object = :object')
            ->setParameters(array(
                'project' => $project,
                'beam'    => $beam,
                'object'  => $object,
            ))
        ;

        if ($user->getGroup()) {
            $customView
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('u.user', ':user'),
                    $qb->expr()->eq('u.group', ':group')
                ))
                ->setParameter('user', $user)
                ->setParameter('group', $user->getGroup())
            ;
        } else {
            $customView
                ->andWhere('u.user = :user')
                ->setParameter('user', $user)
            ;
        }

        return  $customView
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getCustomViewForUser($user, $project, $beam, $object)
    {
        $qb = $this->createQueryBuilder('u');

        $customView = $qb
            ->andWhere('u.project = :project')
            ->andWhere('u.beam = :beam')
            ->andWhere('u.object = :object')
            ->setParameters(array(
                'project' => $project,
                'beam'    => $beam,
                'object'  => $object,
            ))
        ;

        if ($user->getGroup()) {
            $customView
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('u.user', ':user'),
                    $qb->expr()->eq('u.group', ':group')
                ))
                ->setParameter('user', $user)
                ->setParameter('group', $user->getGroup())
            ;
        } else {
            $customView
                ->andWhere('u.user = :user')
                ->setParameter('user', $user)
            ;
        }

        return  $customView
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getCustomViewsForUser($user, $project, $beam, $object)
    {
        $qb = $this->createQueryBuilder('u');

        $customView = $qb
            ->andWhere('u.project = :project')
            ->andWhere('u.beam = :beam')
            ->andWhere('u.object = :object')
            ->setParameters(array(
                'project' => $project,
                'beam'    => $beam,
                'object'  => $object,
            ))
        ;

        if ($user->getGroup()) {
            $customView
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('u.user', ':user'),
                    $qb->expr()->eq('u.group', ':group')
                ))
                ->setParameter('user', $user)
                ->setParameter('group', $user->getGroup())
            ;
        } else {
            $customView
                ->andWhere('u.user = :user')
                ->setParameter('user', $user)
            ;
        }

        return new ArrayCollection($customView
            ->getQuery()
            ->getResult())
        ;
    }

    public function getPreferredTableViews(User $user, Project $project, Beam $beam, ObjectDefinition $object)
    {
        $customViewDefault = false;

        if ($user->getGroup() && $user->getGroup()->getAdmin()) {
            $tableViews = $object->getTableViews();
        } else {
            $customViews = $this->getCustomViewsForUser($user, $project, $beam, $object);

            $tableViews = $customViews->map(function($item) {
                return $item->getTableView();
            });

            $customViewDefault = $customViews->exists(function($key, $item) {
                return $item->getDefault();
            });
        }

        if (($user->getGroup() && $user->getGroup()->getAdmin()) || !$customViewDefault) {
            $defaultTableView = $object->getDefaultTableView();
            if (!$tableViews->contains($defaultTableView)) {
                $tableViews->add($defaultTableView);
            }
        }

        return $tableViews;
    }

    public function getPreferredTableView(User $user, Project $project, Beam $beam, ObjectDefinition $object, $name = null)
    {
        $customViewDefault = false;

        if ($user->getGroup() && $user->getGroup()->getAdmin()) {
            $tableViews = $object->getTableViews();
        } else {
            $customViews = $this->getCustomViewsForUser($user, $project, $beam, $object);

            $tableViews = $customViews->map(function($item) {
                return $item->getTableView();
            });

            $customViewDefault = $customViews->exists(function($key, $item) {
                return $item->getDefault();
            });
        }

        if (($user->getGroup() && $user->getGroup()->getAdmin()) || !$customViewDefault) {
            $defaultTableView = $object->getDefaultTableView();
            if (!$tableViews->contains($defaultTableView)) {
                $tableViews->add($defaultTableView);
            }
        }

        // If there is a custom view requested, try to get it from the collection
        if ($name !== null) {
            $tableView = $tableViews->filter(function($item) use ($name) {
                return $item->getName() == $name;
            })->first();

            if ($tableView) {
                return $tableView;
            }
        } else {
            if (!$customViewDefault) {
                return $object->getDefaultTableView();
            }
        }

        // If the default view is requested, try to get it from the collection
        if ($customViewDefault) {
            $customView = $customViews->filter(function($item) {
                return $item->getDefault() == true;
            })->first();

            if ($customView) {
                return $customView->getTableView();
            }
        }

        // Return the first view the user can access
        if ($tableViews->count() > 0) {
            return $tableViews->first();
        }

        // Return the default view
        return $object->getDefaultTableView();
    }
}
