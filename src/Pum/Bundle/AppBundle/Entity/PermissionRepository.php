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

    public function deleteSubPermissions($attribute, $group, $project, $beam = null, $object = null, $instance = null)
    {
        $this->deletePermissions($attribute, $group, $project, $beam, $object, $instance, false);
    }

    public function deletePermissions($attribute, $group, $project, $beam = null, $object = null, $instance = null, $deleteCurrentLevel = false)
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->delete()
            ->andWhere($qb->expr()->eq('p.group', ':group'))
            ->andWhere($qb->expr()->eq('p.project', ':project'))
            ->setParameter('group', $group)
            ->setParameter('project', $project)
        ;

        if ($attribute) {
            $qb
                ->andWhere($qb->expr()->eq('p.attribute', ':attribute'))
                ->setParameter('attribute', $attribute)
            ;
        }

        if ($beam) {
            $qb
                ->andWhere($qb->expr()->eq('p.beam', ':beam'))
                ->setParameter('beam', $beam)
            ;

            if ($object) {
                $qb
                    ->andWhere($qb->expr()->eq('p.object', ':object'))
                    ->setParameter('object', $object)
                ;

                if ($instance) {
                    $qb
                        ->andWhere($qb->expr()->eq('p.instance', ':instance'))
                        ->setParameter('instance', $instance)
                    ;
                } elseif(false === $deleteCurrentLevel) {
                    $qb->andWhere($qb->expr()->isNotNull('p.instance'));
                }

            } elseif(false === $deleteCurrentLevel) {
                $qb->andWhere($qb->expr()->isNotNull('p.object'));
            }

        } elseif(false === $deleteCurrentLevel) {
            $qb->andWhere($qb->expr()->isNotNull('p.beam'));
        }

        $qb
            ->getQuery()
            ->execute()
        ;
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
