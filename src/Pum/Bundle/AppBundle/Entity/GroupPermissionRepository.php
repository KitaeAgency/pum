<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pum\Core\Definition\Project;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pagerfanta\Pagerfanta;
use Pum\Bundle\AppBundle\Entity\GroupPermission;

class GroupPermissionRepository extends EntityRepository
{
    const PERMISSION_CLASS = 'Pum\Bundle\AppBundle\Entity\GroupPermission';

    public function getInstancesPermissions($user, $project, $beam, $object, $attribute = null)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('p.instance as id')
            ->andWhere($qb->expr()->eq('p.group', ':group'))
            ->andWhere($qb->expr()->eq('p.project', ':project'))
            ->andWhere($qb->expr()->eq('p.beam', ':beam'))
            ->andWhere($qb->expr()->eq('p.object', ':object'))
            ->andWhere($qb->expr()->isNotNull('p.instance'))
            ->setParameters(array(
                'group'   => $user->getGroup(),
                'project' => $project,
                'beam'    => $beam,
                'object'  => $object,
            ))
        ;

        if (null !== $attribute) {
            $qb
                ->andWhere($qb->expr()->eq('p.attribute', ':attribute'))
                ->setParameter('attribute', $attribute)
            ;
        }

        return $qb->getQuery()->getResult();
    }

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

    public function get(Group $group, Project $project, Beam $beam = null, ObjectDefinition $object = null, $instance = null)
    {
        $query = $this->createQueryBuilder('p')
           ->where('p.group = :group')
           ->andWhere('p.project = :project')
           ->setParameters(array(
               ':group' => $group,
               ':project' => $project,
           ));

        if ($beam === null) {
            $query->andWhere('p.beam IS NULL');
        } else {
            $query->andWhere('p.beam = :beam')->setParameter(':beam', $beam);
        }

        if ($object === null) {
            $query->andWhere('p.object IS NULL');
        } else {
            $query->andWhere('p.object = :object')->setParameter(':object', $object);
        }

        if ($instance === null) {
            $query->andWhere('p.instance IS NULL');
        } else {
            $query->andWhere('p.instance = :instance')->setParameter(':instance', $instance);
        }

        $permissions = $query
           ->getQuery()
           ->getResult();

        if (!empty($permissions)) {
            return reset($permissions);
        }
        return null;
    }

    public function save(GroupPermission $groupPermission)
    {
        if ($p = $this->get(
            $permission->getUser(),
            $permission->getProject(),
            $permission->getBeam(),
            $permission->getObject(),
            $permission->getInstance()
        )) {
            $em = $this->getEntityManager();

            if ($p->getAttribute() != $groupPermission->getAttribute()) {
                $em->persist($groupPermission);
                $em->flush();
            }
        }
    }

    public function delete(GroupPermission $groupPermission)
    {
        $em = $this->getEntityManager();
        $em->remove($groupPermission);
        $em->flush();
    }

    public function flush()
    {
        $em = $this->getEntityManager();
        $em->flush();
    }

    public function addPermission($attributes, $group, $project, $beam = null, $object = null, $instance = null)
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
        $groupPermission = $this->get(
            $em->getReference('Pum\Bundle\AppBundle\Entity\Group', $group),
            $em->getReference('Pum\Core\Definition\Project', $project),
            (null === $beam ? null : $em->getReference('Pum\Core\Definition\Beam', $beam)),
            (null === $object ? null : $em->getReference('Pum\Core\Definition\ObjectDefinition', $object)),
            $instance
        );

        if (!$groupPermission) {
            $groupPermission = new GroupPermission();
            $groupPermission
                ->setGroup($em->getReference('Pum\Bundle\AppBundle\Entity\Group', $group))
                ->setProject($em->getReference('Pum\Core\Definition\Project', $project))
                ->setBeam((null === $beam) ? null : $em->getReference('Pum\Core\Definition\Beam', $beam))
                ->setObject((null === $object) ? null : $em->getReference('Pum\Core\Definition\ObjectDefinition', $object))
                ->setInstance($instance)
            ;
        }

        $groupPermission->setAttributes($attributes);
        $em->persist($groupPermission);
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
                } elseif (false === $deleteCurrentLevel) {
                    $qb->andWhere($qb->expr()->isNotNull('p.instance'));
                }

            } elseif (false === $deleteCurrentLevel) {
                $qb->andWhere($qb->expr()->isNotNull('p.object'));
            }

        } elseif (false === $deleteCurrentLevel) {
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
