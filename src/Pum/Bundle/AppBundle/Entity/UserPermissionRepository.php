<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pum\Core\Definition\Project;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Bundle\AppBundle\Entity\Permission;
use Pum\Bundle\AppBundle\Entity\User;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Pagerfanta\Pagerfanta;

class UserPermissionRepository extends EntityRepository
{
    const PERMISSION_CLASS = 'Pum\Bundle\AppBundle\Entity\UserPermission';

    public function getUserPermissions(User $user, $withInstance = true)
    {
        if (!$user->getGroup()) {
            return $user->getAdvancedPermissions();
        }

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Pum\Bundle\AppBundle\Entity\UserPermission', 'permission');
        $rsm->addJoinedEntityResult('Pum\Bundle\AppBundle\Entity\User', 'user', 'permission', 'user');
        $rsm->addJoinedEntityResult('Pum\Core\Definition\Project', 'project', 'permission', 'project');
        $rsm->addJoinedEntityResult('Pum\Core\Definition\Beam', 'beam', 'permission', 'beam');
        $rsm->addJoinedEntityResult('Pum\Core\Definition\ObjectDefinition', 'object', 'permission', 'object');
        $rsm->addFieldResult('permission', 'id', 'id');
        $rsm->addFieldResult('permission', 'attribute', 'attribute');
        $rsm->addFieldResult('user', 'user_id', 'id');
        $rsm->addFieldResult('project', 'project_id', 'id');
        $rsm->addFieldResult('beam', 'beam_id', 'id');
        $rsm->addFieldResult('object', 'object_id', 'id');
        $rsm->addFieldResult('permission', 'instance_id', 'instance');

        $queryString = "
                SELECT  (@cnt := @cnt + 1) as id,
                        p.project_id,
                        p.beam_id,
                        p.object_id,
                        p.attribute,
                        p.instance_id,
                        :user as user_id
                FROM (
                    SELECT
                        g.project_id AS project_id,
                        g.beam_id AS beam_id,
                        g.object_id AS object_id,
                        g.attribute AS attribute,
                        g.instance_id AS instance_id
                    FROM  `ww_permission` g
                    WHERE g.group_id = :group
                    UNION ALL
                    SELECT
                        u.project_id,
                        u.beam_id,
                        u.object_id,
                        u.attribute,
                        u.instance_id
                    FROM  `ww_user_permission` u
                    WHERE u.user_id = :user
                ) AS p
                CROSS JOIN (SELECT @cnt := 0) AS dummy
                GROUP BY p.project_id, p.beam_id, p.object_id, p.attribute, p.instance_id
            ";

        $query = $this->_em->createNativeQuery($queryString, $rsm);
        $query->setParameters(array(
            'user' => $user,
            'group' => $user->getGroup()
        ));

        return $query->getResult();
    }

    public function getPage($page = 1)
    {
        $page = max(1, (int) $page);

        $pager = new Pagerfanta(new DoctrineORMAdapter($this->createQueryBuilder('u')->orderBy('u.id', 'ASC')));
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function get(User $user, Project $project, $attribute, Beam $beam = null, ObjectDefinition $object = null, $instance = null)
    {
        $permissions = $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.project = :project')
            ->andWhere('p.attribute = :attribute')
            ->andWhere('p.beam = :beam')
            ->andWhere('p.object = :object')
            ->andWhere('p.instance = :instance')
            ->setParameters(array(
                ':user' => $user,
                ':project' => $project,
                ':attribute' => $attribute,
                ':beam' => $beam,
                ':object' => $object,
                ':instance' => $instance,
            ))
            ->getQuery()
            ->getResult();

        if (!empty($permissions)) {
            return reset($permissions);
        }
        return null;
    }

    public function save(UserPermission $permission)
    {
        if (!$this->get(
            $permission->getUser(),
            $permission->getProject(),
            $permission->getAttribute(),
            $permission->getBeam(),
            $permission->getObject(),
            $permission->getInstance()
        )) {
            $em = $this->getEntityManager();
            $em->persist($permission);
            $em->flush();
        }
    }

    public function delete(UserPermission $permission)
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

    public function addPermission($attribute, $user, $project, $beam = null, $object = null, $instance = null)
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

        $permission = new UserPermission();
        $permission
            ->setAttribute($attribute)
            ->setUser($em->getReference('Pum\Bundle\AppBundle\Entity\User', $user))
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
